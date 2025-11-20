<?php
/**
 * @class Image
 *
 * This is a helper class to deal with the images.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Image
{

    protected $url;
    protected $width;
    protected $height;
    protected $mime;

    /**
     * The constructor of the object.
     */
    public function __construct($file)
    {
        if (is_file($file)) {
            $info = getimagesize($file);
            $this->url = $file;
            $this->width = $info[0];
            $this->height = $info[1];
            $this->mime = $info['mime'];
        }
    }

    /**
     * Return an information of the image.
     */
    public function get($item)
    {
        return (isset($this->$item)) ? $this->$item : false;
    }

    /**
     * Return the public url of the image.
     */
    public function getUrl()
    {
        return str_replace(ASTERION_LOCAL_FILE, ASTERION_LOCAL_URL, $this->get('url'));
    }

    /**
     * Return the image extension.
     */
    public function getExtension()
    {
        return Image::extension($this->mime);
    }

    /**
     * Return the image extension.
     */
    static public function extension($mime)
    {
        switch ($mime) {
            case 'image/jpg':
            case 'image/jpeg':
                return 'jpg';
                break;
            case 'image/gif':
                return 'gif';
                break;
            case 'image/png':
                return 'png';
                break;
        }
    }

    /**
     * Return the type of the image.
     */
    public static function getType($mime)
    {
        $type = explode('/', $mime);
        $type = $type[1];
        if ($type == 'jpg') {$type = 'jpeg';}
        if ($type != '' && $type != 'jpeg' && $type != 'png' && $type != 'gif') {
            throw new Exception('Cannot resize image. Mime:' . $mime);
        }
        return $type;
    }

    /**
     * Return the file name of the image.
     */
    public function getFileName()
    {
        $file = explode('.', basename($this->url));
        return $file[0];
    }

    /**
     * Convert an image to JPG.
     */
    public function toJpg()
    {
        if ($this->getExtension() != 'jpg') {
            $extension = $this->getExtension();
            if ($extension != '') {
                $function = 'imagecreatefrom' . $extension;
                $image = $function($this->get('url'));
                $fileDestinationArray = explode('.', $this->get('url'));
                $fileDestination = $fileDestinationArray[0] . '.jpg';
                imagejpeg($image, $fileDestination, 100);
                imagedestroy($image);
                unlink($this->get('url'));
                $this->url = $fileDestination;
                $this->mime = 'image/jpeg';
            } else {
                return false;
            }
        }
        return true;
    }

    public function toWebp($inputFile, $outputFile, $quality = 100)
    {
        $fileType = exif_imagetype($inputFile);
        switch ($fileType) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($inputFile);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($inputFile);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($inputFile);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_WEBP:
                rename($inputFile, $outputFile);
                return;
            default:
                return;
        }
        imagewebp($image, $outputFile, $quality);
        imagedestroy($image);
    }

    /**
     * Resize an image.
     */
    public function resize($fileDestination, $newWidth, $maxHeight, $mime)
    {
        $fileOrigin = $this->get('url');
        $type = $this->getType($mime);
        $function = 'imagecreatefrom' . $type;
        $image = $function($fileOrigin);
        $widthImage = imagesx($image);
        $heightImage = imagesy($image);
        if ($widthImage < $newWidth) {
            if (!copy($fileOrigin, $fileDestination) && ASTERION_DEBUG) {
                throw new Exception('Cannot copy from ' . $fileOrigin . ' to ' . $fileDestination);
            }
        } else {
            $newHeight = ceil(($newWidth * $heightImage) / $widthImage);
            if ($newHeight > $maxHeight) {
                $newHeight = $maxHeight;
                $newWidth = ceil(($newHeight * $widthImage) / $heightImage);
            }
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $widthImage, $heightImage);
            $function = 'image' . $type;
            $function($newImage, $fileDestination, 100);
            imagedestroy($newImage);
            imagedestroy($image);
        }
    }

    /**
     * Convert an image into grayscale.
     */
    public function grayscale($fileDestination)
    {
        $fileOrigin = $this->get('url');
        $type = $this->getType($mime);
        $function = "imagecreatefrom" . $type;
        $image = $function($fileOrigin);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        for ($i = 0; $i < $imageWidth; $i++) {
            for ($j = 0; $j < $imageHeight; $j++) {
                $rgb = imagecolorat($image, $i, $j);
                $rr = ($rgb >> 16) & 0xFF;
                $gg = ($rgb >> 8) & 0xFF;
                $bb = $rgb & 0xFF;
                $g = round(($rr + $gg + $bb) / 3);
                $val = imagecolorallocate($image, $g, $g, $g);
                imagesetpixel($image, $i, $j, $val);
            }
        }
        imagejpeg($image, $fileDestination, 100);
    }

    /**
     * Resize an image an cut the borders to create a perfect square.
     */
    public function resizeSquare($fileDestination, $newSide, $mime)
    {
        $fileOrigin = $this->get('url');
        $type = $this->getType($mime);
        $function = "imagecreatefrom" . $type;
        $image = $function($fileOrigin);
        $widthImage = imagesx($image);
        $heightImage = imagesy($image);
        if ($widthImage > $heightImage) {
            $relation = $heightImage / $widthImage;
            $newWidth = intval($newSide / $relation);
            $newHeight = $newSide;
            $left = intval(($newWidth - $newSide) / 2);
            $top = 0;
        } else {
            $relation = $widthImage / $heightImage;
            $newWidth = $newSide;
            $newHeight = intval($newSide / $relation);
            $left = 0;
            $top = intval(($newHeight - $newSide) / 2);
        }
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresized($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $widthImage, $heightImage);
        $squareImage = imagecreatetruecolor($newSide, $newSide);
        imagecopyresized($squareImage, $newImage, 0, 0, $left, $top, $newSide, $newSide, $newSide, $newSide);
        $function = "image" . $type;
        $function($squareImage, $fileDestination, 100);
        imagedestroy($squareImage);
        imagedestroy($image);
    }

    /**
     * Add a PNG with transparency over an image, both images are the same size and need no margins.
     */
    public function addPngOverImage($pngFile, $fileDestination, $mime)
    {
        $fileOrigin = $this->get('url');
        $type = $this->getType($mime);
        $function = "imagecreatefrom" . $type;
        $image = $function($fileOrigin);
        
        $overlay = imagecreatefrompng($pngFile);
        imagealphablending($overlay, true);
        imagesavealpha($overlay, true);
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        imagecopy($image, $overlay, 0, 0, 0, 0, $width, $height);
        
        $function = "image" . $type;
        $function($image, $fileDestination, 100);
        
        imagedestroy($overlay);
        imagedestroy($image);
    }
    
    /**
     * Write a text over an image.
     * The text is centered both horizontally and vertically with a 20% margin on all sides.
     * It uses white text with a black shadow (15% of font size) for better visibility.
     */
    public function addTextOverImage($text, $fileDestination, $mime, $fontSize = 100, $fontFile = null)
    {
        $fileOrigin = $this->get('url');
        $type = $this->getType($mime);
        $function = "imagecreatefrom" . $type;
        $image = $function($fileOrigin);
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Calculate margins (20% on all sides)
        $marginX = $width * 0.2;
        $marginY = $height * 0.2;
        $textAreaWidth = $width - ($marginX * 2);
        $textAreaHeight = $height - ($marginY * 2);
        
        // Calculate shadow offset (15% of font size)
        $shadowOffset = $fontSize * 0.08;
                
        // Allocate colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        if ($fontFile && is_file($fontFile)) {
            // Use TrueType font - wrap text to fit width
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            
            foreach ($words as $word) {
                $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
                $bbox = imagettfbbox($fontSize, 0, $fontFile, $testLine);
                $testWidth = abs($bbox[4] - $bbox[0]);
                
                if ($testWidth > $textAreaWidth && $currentLine !== '') {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
            
            // Calculate line height and total text height
            $bbox = imagettfbbox($fontSize, 0, $fontFile, 'Ay');
            $lineHeight = $fontSize * 1.1; // 10% line spacing
            $totalTextHeight = count($lines) * $lineHeight;
            
            // Start Y position (aligned at top with margin)
            $startY = $marginY + abs($bbox[5] - $bbox[1]);
            
            // Draw each line
            foreach ($lines as $i => $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontFile, $line);
                $textWidth = abs($bbox[4] - $bbox[0]);
                $x = ($width - $textWidth) / 2;
                $y = $startY + ($i * $lineHeight);
                
                // Draw shadow
                imagettftext($image, $fontSize, 0, $x + $shadowOffset, $y + $shadowOffset, $black, $fontFile, $line);
                // Draw white text
                imagettftext($image, $fontSize, 0, $x, $y, $white, $fontFile, $line);
            }
        } else {
            // Use built-in font (font 5 is the largest)
            $fontWidth = imagefontwidth(5);
            $fontHeight = imagefontheight(5);
            
            // Wrap text to fit width
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            
            foreach ($words as $word) {
                $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
                $testWidth = $fontWidth * strlen($testLine);
                
                if ($testWidth > $textAreaWidth && $currentLine !== '') {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
            
            // Calculate total text height with line spacing
            $lineHeight = $fontSize * 1.1;
            $totalTextHeight = count($lines) * $lineHeight;
            
            // Start Y position (aligned at top with margin)
            $startY = $marginY;
            
            // Draw each line
            $builtInShadowOffset = max(1, round($fontHeight * 0.15));
            foreach ($lines as $i => $line) {
                $textWidth = $fontWidth * strlen($line);
                $x = ($width - $textWidth) / 2;
                $y = $startY + ($i * $lineHeight);
                
                // Draw shadow
                imagestring($image, 5, $x + $builtInShadowOffset, $y + $builtInShadowOffset, $line, $black);
                // Draw white text
                imagestring($image, 5, $x, $y, $line, $white);
            }
        }
        
        $function = "image" . $type;
        $function($image, $fileDestination, 100);
        
        imagedestroy($image);
    }

}
