<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper\SiteMap;

/**
 * Class SiteMap.
 *
 * @link http://www.sitemaps.org/
 */
class SiteMap extends AbstractSiteMap
{
    /** @var array  */
    protected static $frequency = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    /**
     * @param string                    $location
     * @param float|string|int          $priority
     * @param int|string|\DateTime|null $lastModification
     * @param string|null               $changeFrequency
     *
     * @return static
     */
    public function addUrl($location, $priority = 0.5, $lastModification = null, $changeFrequency = null)
    {
        $urlElement = $this->createElement('url');

        if (0 === strpos($location, '/')) {
            $location = $this->baseUri.$location;
        }
        $locElement = $this->createElement('loc');
        $locElement->appendChild($this->createTextNode($location));
        $urlElement->appendChild($locElement);

        $priorityElement = $this->createElement('priority');
        $priorityElement->appendChild($this->createTextNode((string) round($priority, 1)));
        $urlElement->appendChild($priorityElement);

        if (null !== $lastModification) {
            if ($lastModification instanceof \DateTime) {
                $lastModification = $lastModification->format('c');
            } else {
                if (is_string($lastModification)) {
                    $lastModification = strtotime($lastModification);
                }
                $lastModification = date('c', $lastModification);
            }

            $lastmodElement = $this->createElement('lastmod');
            $lastmodElement->appendChild($this->createTextNode($lastModification));
            $urlElement->appendChild($lastmodElement);
        }

        if (null !== $changeFrequency && in_array($changeFrequency, static::$frequency)) {
            $changefreqElement = $this->createElement('changefreq');
            $changefreqElement->appendChild($this->createTextNode($changeFrequency));
            $urlElement->appendChild($changefreqElement);
        }

        $this->root->appendChild($urlElement);

        return $this;
    }
}
