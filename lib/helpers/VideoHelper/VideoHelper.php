<?php
class VideoHelper
{

    public static function show($link, $options = [])
    {
        $width = (isset($options['width'])) ? $options['width'] : '600';
        $height = (isset($options['height'])) ? $options['height'] : '400';
        if (strpos($link, 'youtube') !== false) {
            $id = substr($link, strpos($link, "v=") + 2, 11);
            return ($id != '') ? '
	        	<iframe width="' . $width . '" height="' . $height . '" src="https://www.youtube.com/embed/' . $id . '?controls=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' : '';
        }
        if (strpos($link, 'vimeo') !== false) {
            sscanf(parse_url($link, PHP_URL_PATH), '/%d', $info);
            $id = intval($info);
            return ($id != '') ? '
	        	<iframe src="https://player.vimeo.com/video/' . $id . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' : '';

        }
    }

    public static function showAmp($link, $options = [])
    {
        if (strpos($link, 'youtube') !== false) {
            $id = substr($link, strpos($link, "v=") + 2, 11);
            $width = (isset($options['width'])) ? $options['width'] : '600';
            $height = (isset($options['height'])) ? $options['height'] : '400';
            return '<amp-youtube data-videoid="' . $id . '" layout="responsive" width="' . $width . '" height="' . $height . '"></amp-youtube>';
        }
    }

    public static function image($link)
    {
        if (strpos($link, 'youtube') !== false) {
            $id = substr($link, strpos($link, "v=") + 2, 11);
            if ($id != '') {
                return '<img src="http://img.youtube.com/vi/' . $id . '/1.jpg" alt="" rel="' . url('modal/show-video/youtube_' . $id) . '"/>';
            }
        }
        if (strpos($link, 'vimeo') !== false) {
            sscanf(parse_url($link, PHP_URL_PATH), '/%d', $info);
            $id = intval($info);
            if ($id != '') {
                $info = json_decode(file_get_contents('http://vimeo.com/api/v2/video/' . $id . '.json'));
                return '<img src="' . $info[0]->thumbnail_medium . '" alt="" rel="' . url('modal/show-video/vimeo_' . $id) . '"/>';
            }
        }
    }

}
