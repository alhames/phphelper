<?php

namespace PhpHelper;

/**
 * Class HtmlFilter.
 */
class HtmlFilter
{
    /** @var string */
    protected $cutTag;

    /** @var array */
    protected $allowedTags = [];

    /** @var array */
    protected $allowedClasses = [];

    /** @var array */
    protected $allowedStyles = [];

    /** @var bool */
    protected $trim = true;

    /** @var bool */
    protected $specialChars = true;

    /** @var string */
    protected $cacheDir;

    /** @var array */
    protected $tidyConfig = [
        // HTML, XHTML, XML Options
        'drop-font-tags' => true,
        'drop-proprietary-attributes' => true,
        'escape-cdata' => true,
        'hide-comments' => true,
        'logical-emphasis' => true,
        'output-html' => true,
        'preserve-entities' => true,
        'show-body-only' => true,
        // Pretty Print Options
        'wrap' => 0,
        'wrap-php' => false,
        'wrap-sections' => false,
        // Character Encoding Options
        'char-encoding' => 'utf8',
        'input-encoding' => 'utf8',
        'newline' => 'LF',
        'output-bom' => false,
        'output-encoding' => 'utf8',
    ];

    /** @var \HTMLPurifier */
    protected $purifier;

    /**
     * StringFilter constructor.
     *
     * @param array  $config
     * @param string $cacheDir
     */
    public function __construct(array $config, string $cacheDir)
    {
        if (isset($config['tags'])) {
            $this->allowedTags = $config['tags'];
        }
        if (isset($config['classes'])) {
            $this->allowedClasses = $config['classes'];
        }
        if (isset($config['styles'])) {
            $this->allowedStyles = $config['styles'];
        }
        if (isset($config['tidy'])) {
            $this->tidyConfig = array_merge($this->tidyConfig, $config['tidy']);
        }
        if (isset($config['cut'])) {
            $this->cutTag = $config['cut'];
            if (!in_array($this->cutTag, $this->allowedTags)) {
                $this->allowedTags[] = $this->cutTag;
            }
            if (!isset($this->tidyConfig['new-blocklevel-tags'])) {
                $this->tidyConfig['new-blocklevel-tags'] = $this->cutTag;
            } elseif (false === strpos($this->tidyConfig['new-blocklevel-tags'], $this->cutTag)) {
                $this->tidyConfig['new-blocklevel-tags'] .= ','.$this->cutTag;
            }
        }
        if (isset($config['trim'])) {
            $this->trim = (bool) $config['trim'];
        }
        if (isset($config['special_chars'])) {
            $this->specialChars = (bool) $config['special_chars'];
        }

        $this->cacheDir = $cacheDir;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function filter(string $string): string
    {
        $string = $this->filterHtmlByTidy($string);
        $string = $this->filterHtmlByPurifier($string);
        if (!$this->specialChars) {
            $string = Str::filter($string, Str::FILTER_TEXT | Str::FILTER_PUNCTUATION | Str::FILTER_SPACE);
        }
        if ($this->trim) {
            $string = trim($string);
        }

        return $string;
    }

    /**
     * @see http://htmlpurifier.org/live/configdoc/plain.html
     *
     * @param string $string
     *
     * @return string
     */
    protected function filterHtmlByPurifier(string $string): string
    {
        if (null === $this->purifier) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', implode(',', $this->allowedTags));
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('AutoFormat.RemoveEmpty', true);
            if (in_array('span', $this->allowedTags, true)) {
                $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
            }
            $config->set('Attr.AllowedClasses', $this->allowedClasses);
            $config->set('CSS.AllowedProperties', $this->allowedStyles);
            $config->set('Cache.SerializerPath', $this->cacheDir);
            if (null !== $this->cutTag) {
                $def = $config->getHTMLDefinition(true);
                $def->addElement($this->cutTag, 'Block', 'Flow', 'Common');
            }
            $this->purifier = new \HTMLPurifier($config);
        }

        return $this->purifier->purify($string);
    }

    /**
     * @see http://tidy.sourceforge.net/docs/quickref.html
     *
     * @param string $string
     *
     * @return string
     */
    protected function filterHtmlByTidy(string $string): string
    {
        $tidy = new \tidy();
        $tidy->parseString($string, $this->tidyConfig, 'utf8');
        $tidy->cleanRepair();

        return (string) $tidy;
    }
}
