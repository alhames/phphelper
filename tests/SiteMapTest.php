<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper\Tests;

use PhpHelper\SiteMap\SiteIndex;
use PhpHelper\SiteMap\SiteMap;

/**
 * Class SiteMapTest.
 */
class SiteMapTest extends \PHPUnit\Framework\TestCase
{
    protected static $xmlMap = '<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">%s</urlset>
';
    protected static $indexXml = '<?xml version="1.0" encoding="utf-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"><sitemap>%s</sitemap></sitemapindex>
';

    /**
     * @dataProvider siteMapProvider
     *
     * @param       $expected
     * @param       $location
     * @param float $priority
     * @param null  $lastModification
     * @param null  $changeFrequency
     */
    public function testSiteMap($expected, $location, $priority = 0.5, $lastModification = null, $changeFrequency = null)
    {
        $sitemap = new SiteMap('http://www.example.com');
        $sitemap->addUrl($location, $priority, $lastModification, $changeFrequency);
        $this->assertSame(sprintf(static::$xmlMap, $expected), $sitemap->saveXML());
    }

    /**
     * @return array
     */
    public function siteMapProvider()
    {
        $pattern = '<url><loc>%s</loc><priority>%s</priority>%s</url>';

        $data = [
            [sprintf($pattern, 'http://www.example.com/', '0.5', ''), '/'],
            [sprintf($pattern, 'http://www.example.com/', '0.5', '<changefreq>hourly</changefreq>'), '/', 0.5, null, 'hourly'],
            [sprintf($pattern, 'http://www.example.com/', '0.5', ''), '/', 0.5, null, 'test'],
        ];
        $locations = [
            '/' => 'http://www.example.com/',
            '/abc' => 'http://www.example.com/abc',
            '/abc.php' => 'http://www.example.com/abc.php',
            '/abc.php?query=value' => 'http://www.example.com/abc.php?query=value',
            '/abc.php?arg1=1&arg2=2' => 'http://www.example.com/abc.php?arg1=1&amp;arg2=2',
            '/'.urlencode('слово') => 'http://www.example.com/'.urlencode('слово'),
            'http://www.site.com/' => 'http://www.site.com/',
            'https://site.com/test' => 'https://site.com/test',
        ];
        $priorities = [
            [0.5, '0.5'],
            [1, '1'],
            [1.0, '1'],
            [0, '0'],
            [0.1, '0.1'],
            [1 / 3, '0.3'],
            [0.54321, '0.5'],
            [0.67, '0.7'],
            ['0.8', '0.8'],
        ];
        $time = time();
        $dates = [
            [null, null],
            [$time, date('c', $time)],
            [(new \DateTime())->setTimestamp($time), date('c', $time)],
            [date('d-m-Y H:i:s', $time), date('c', $time)],
            [date('c', $time), date('c', $time)],
        ];

        foreach ($locations as $actualLocation => $expectedLocation) {
            foreach ($priorities as $priority) {
                foreach ($dates as $date) {
                    $extra = '';
                    if (null !== $date[1]) {
                        $extra .= '<lastmod>'.$date[1].'</lastmod>';
                    }
                    $data[] = [
                        sprintf($pattern, $expectedLocation, $priority[1], $extra),
                        $actualLocation,
                        $priority[0],
                        $date[0],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @dataProvider siteIndexProvider
     *
     * @param      $expected
     * @param      $location
     * @param null $lastModification
     */
    public function testSiteIndex($expected, $location, $lastModification = null)
    {
        $siteIndex = new SiteIndex('http://www.example.com');
        $siteIndex->addSiteMap($location, $lastModification);
        $this->assertSame(sprintf(static::$indexXml, $expected), $siteIndex->saveXML());
    }

    /**
     * @return array
     */
    public function siteIndexProvider()
    {
        $time = time();
        $lastMod = '<lastmod>'.date('c', $time).'</lastmod>';

        return [
            ['<loc>http://www.example.com/sitemap.xml</loc>', '/sitemap.xml'],
            ['<loc>https://test.com/sitemap.xml</loc>', 'https://test.com/sitemap.xml'],
            ['<loc>http://www.example.com/</loc>'.$lastMod, '/', $time],
            ['<loc>http://www.example.com/</loc>'.$lastMod, '/', (new \DateTime())->setTimestamp($time)],
            ['<loc>http://www.example.com/</loc>'.$lastMod, '/', date('d-m-Y H:i:s', $time)],
        ];
    }
}
