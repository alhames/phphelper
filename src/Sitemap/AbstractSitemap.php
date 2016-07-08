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
 * Class AbstractSitemap.
 */
abstract class AbstractSitemap extends \DOMDocument
{
    /** @var string */
    protected $baseUri;

    /** @var \DOMElement */
    protected $root;

    /**
     * @param string $baseUri
     */
    public function __construct($baseUri = null)
    {
        parent::__construct('1.0', 'utf-8');
        $this->baseUri = rtrim($baseUri, '/');

        $type = ($this instanceof SiteIndex) ? 'siteindex' : 'sitemap';
        $this->root = $this->createElement('siteindex' === $type ? 'sitemapindex' : 'urlset');

        $this->root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->root->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $this->root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->root->setAttribute(
            'xsi:schemaLocation',
            'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/'.$type.'.xsd'
        );

        $this->appendChild($this->root);
    }
}
