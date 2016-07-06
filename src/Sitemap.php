<?php

namespace PhpHelper;

/**
 * Class Sitemap.
 *
 * @link http://www.sitemaps.org/
 */
class Sitemap extends \DOMDocument
{
    /** @var array  */
    protected static $attributes = [
        'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
        'xmlns:xhtml' => 'http://www.w3.org/1999/xhtml',
        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        'xsi:schemaLocation' => 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
    ];

    /** @var array  */
    protected static $frequency = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    /** @var \DOMElement */
    protected $urlSet;

    /** @var string */
    protected $baseUri;

    /**
     * @param string $baseUri
     */
    function __construct($baseUri = null)
    {
        parent::__construct('1.0', 'utf-8');

        if (null !== $baseUri) {
            if (!Str::isUrl($baseUri, true)) {
                throw new Exception\InvalidArgumentException('Base uri must be an url.');
            }

            $this->baseUri = rtrim($baseUri, '/');
        }

        $this->urlSet = $this->createElement('urlset');
        foreach (static::$attributes as $attr => $value) {
            $this->urlSet->setAttribute($attr, $value);
        }

        $this->appendChild($this->urlSet);
    }

    /**
     * @param string               $location
     * @param float                $priority
     * @param int|string|\DateTime $lastModification
     * @param string               $changeFrequency
     *
     * @return self
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

        if (!is_numeric($priority) || $priority < 0 || $priority > 1) {
            throw new Exception\InvalidArgumentException('Priority must be a float in range from 0.0 to 1.0.');
        }
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

        if (null !== $changeFrequency) {
            if (!in_array($changeFrequency, static::$frequency)) {
                throw new Exception\InvalidArgumentException('Change frequency is invalid.');
            }
            $changefreqElement = $this->createElement('changefreq');
            $changefreqElement->appendChild($this->createTextNode($changeFrequency));
            $urlElement->appendChild($changefreqElement);
        }

        $this->urlSet->appendChild($urlElement);

        return $this;
    }
}
