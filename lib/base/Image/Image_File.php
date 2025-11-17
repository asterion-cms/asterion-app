<?php
/**
 * @class ImageFile
 *
 * This is a helper class to deal with the image files.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Image_File
{

    /**
     * Save an image for an object.
     */
    public static function saveImageObject($fileImage, $objectName, $fileName)
    {
        return Image_File::saveImage($fileImage, ASTERION_STOCK_FILE . $objectName, $fileName);
    }

    /**
     * Save the image and create versions of itself.
     */
    public static function saveImage($fileImage, $fileFolder, $fileName)
    {
        if ($fileImage != '' && $fileFolder != '' && $fileName != '') {
            $fileName = Text::simpleUrlFileBase($fileName);
            $folder = $fileFolder . '/' . $fileName;
            File::deleteDirectory($folder);
            File::createDirectory($fileFolder);
            File::createDirectory($folder);
            $fileImage = str_replace(ASTERION_STOCK_URL, ASTERION_STOCK_FILE, $fileImage);
            $informationImage = getimagesize($fileImage);
            if (isset($informationImage['mime']) && Image::extension($informationImage['mime']) != '') {
                $destination = $folder . '/' . $fileName . '.' . Image::extension($informationImage['mime']);
                if (@copy($fileImage, $destination)) {
                    @chmod($destination, 0777);
                    $image = new Image($destination);
                    if ($image->toJpg()) {
                        if (ASTERION_SAVE_IMAGE_HUGE) {
                            $fileHuge = $folder . '/' . $image->getFileName() . '_huge.jpg';
                            $image->resize($fileHuge, ASTERION_WIDTH_HUGE, ASTERION_HEIGHT_MAX_HUGE, $image->get('mime'));
                            @chmod($fileHuge, 0777);
                            if (ASTERION_SAVE_WEBP) {
                                $image->toWebp($fileHuge, $fileHuge . '.webp');
                                @chmod($fileHuge . '.webp', 0777);
                            }
                        }
                        if (ASTERION_SAVE_IMAGE_WEB) {
                            $fileWeb = $folder . '/' . $image->getFileName() . '_web.jpg';
                            $image->resize($fileWeb, ASTERION_WIDTH_WEB, ASTERION_HEIGHT_MAX_WEB, $image->get('mime'));
                            @chmod($fileWeb, 0777);
                            if (ASTERION_SAVE_WEBP) {
                                $image->toWebp($fileWeb, $fileWeb . '.webp');
                                @chmod($fileWeb . '.webp', 0777);
                            }
                        }
                        if (ASTERION_SAVE_IMAGE_SMALL) {
                            $fileSmall = $folder . '/' . $image->getFileName() . '_small.jpg';
                            $image->resize($fileSmall, ASTERION_WIDTH_SMALL, ASTERION_HEIGHT_MAX_SMALL, $image->get('mime'));
                            @chmod($fileSmall, 0777);
                            if (ASTERION_SAVE_WEBP) {
                                $image->toWebp($fileSmall, $fileSmall . '.webp');
                                @chmod($fileSmall . '.webp', 0777);
                            }
                        }
                        if (ASTERION_SAVE_IMAGE_THUMB) {
                            $fileThumb = $folder . '/' . $image->getFileName() . '_thumb.jpg';
                            $image->resize($fileThumb, ASTERION_WIDTH_THUMB, ASTERION_HEIGHT_MAX_THUMB, $image->get('mime'));
                            @chmod($fileThumb, 0777);
                            if (ASTERION_SAVE_WEBP) {
                                $image->toWebp($fileThumb, $fileThumb . '.webp');
                                @chmod($fileThumb . '.webp', 0777);
                            }
                        }
                        if (ASTERION_SAVE_IMAGE_SQUARE) {
                            $fileSquare = $folder . '/' . $image->getFileName() . '_square.jpg';
                            $image->resizeSquare($fileSquare, ASTERION_WIDTH_SQUARE, $image->get('mime'));
                            @chmod($fileSquare, 0777);
                            if (ASTERION_SAVE_WEBP) {
                                $image->toWebp($fileSquare, $fileSquare . '.webp');
                                @chmod($fileSquare . '.webp', 0777);
                            }
                        }
                        if (!ASTERION_SAVE_IMAGE_ORIGINAL) {
                            @unlink($destination);
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Delete an entire image folder.
     */
    public static function deleteImage($objectName, $name)
    {
        $directory = ASTERION_STOCK_FILE . $objectName . '/' . $name . '/';
        if ($name != '' && is_dir($directory)) {
            rrmdir($directory);
        }
    }

}
