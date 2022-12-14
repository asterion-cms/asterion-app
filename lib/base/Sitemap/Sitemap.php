<?php
/**
 * @class Sitemap
 *
 * This is a helper class to generate sitemaps.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Sitemap
{

    /**
     * Generate the sitemap for the site.
     */
    public static function generate($urls = [])
    {
        $urlsXml = '';
        foreach ($urls as $url) {
            $urlsXml .= '<url><loc>' . $url . '</loc></url>';
        }
        return '
        	<!--?xml version="1.0" encoding="UTF-8"?-->
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
				' . $urlsXml . '
			</urlset>';
    }

    /**
     * Generate an array with the URLs of a list of objects.
     */
    public static function getUrls($objects = [])
    {
        $urls = [];
        foreach ($objects as $object) {
            $urls[] = $object->url();
        }
        return $urls;
    }

    /**
     * Function to get the sitemap URLs of an object.
     */
    public static function sitemapUrls($objectName)
    {
        $object = new $objectName();
        return Sitemap::getUrls($object->readList());
    }

}
