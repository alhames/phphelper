<?php
use PhpHelper\Sitemap;

/**
 * Class SitemapTest.
 */
class SitemapTest extends \PHPUnit_Framework_TestCase
{
    protected static $xml = '<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">%s</urlset>
';

    /**
     * @dataProvider defaultProvider
     *
     * @param       $expected
     * @param       $location
     * @param float $priority
     * @param null  $lastModification
     * @param null  $changeFrequency
     */
    public function testDefault($expected, $location, $priority = 0.5, $lastModification = null, $changeFrequency = null)
    {
        $sitemap = new Sitemap('http://www.example.com');
        $sitemap->addUrl($location, $priority, $lastModification, $changeFrequency);
        $this->assertSame(sprintf(static::$xml, $expected), $sitemap->saveXML());
    }

    /**
     * @return array
     */
    public function defaultProvider()
    {
        return [
            ['<url><loc>http://www.example.com/</loc><priority>0.5</priority></url>', '/'],
            ['<url><loc>http://www.example.com/</loc><priority>1</priority></url>', '/', 1],
            ['<url><loc>http://www.example.com/</loc><priority>1</priority></url>', '/', 1.0],
            ['<url><loc>http://www.example.com/</loc><priority>0</priority></url>', '/', 0],
            ['<url><loc>http://www.example.com/</loc><priority>0.1</priority></url>', '/', 0.1],
            ['<url><loc>http://www.example.com/</loc><priority>0.3</priority></url>', '/', 1/3],
            ['<url><loc>http://www.example.com/</loc><priority>0.5</priority></url>', '/', 0.54321],
            ['<url><loc>http://www.example.com/</loc><priority>0.7</priority></url>', '/', 0.67],
            ['<url><loc>http://www.example.com/</loc><priority>1</priority><lastmod>'.date('c', strtotime('now')).'</lastmod></url>', '/', 1, 'now'],
            ['<url><loc>http://www.example.com/</loc><priority>1</priority><lastmod>'.date('c').'</lastmod></url>', '/', 1, time()],
            ['<url><loc>http://www.example.com/</loc><priority>1</priority><lastmod>'.date('c').'</lastmod></url>', '/', 1, new DateTime()],
        ];
    }
}
