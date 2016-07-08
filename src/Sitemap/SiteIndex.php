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
 * Class SitemapIndex.
 */
class SiteIndex extends AbstractSitemap
{
    /**
     * @param string               $location
     * @param int|string|\DateTime $lastModification
     *
     * @return self
     */
    public function addSitemap($location, $lastModification = null)
    {
        $sitemapElement = $this->createElement('sitemap');

        if (0 === strpos($location, '/')) {
            $location = $this->baseUri.$location;
        }
        $locElement = $this->createElement('loc');
        $locElement->appendChild($this->createTextNode($location));
        $sitemapElement->appendChild($locElement);

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
            $sitemapElement->appendChild($lastmodElement);
        }

        $this->root->appendChild($sitemapElement);

        return $this;
    }
}
