<?php

/*
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer
 */

$header = <<<EOF
This file is part of the PHP Helper package.

(c) Pavel Logachev <alhames@mail.ru>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return Symfony\CS\Config\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers(array(
        'header_comment',
        'ordered_use',
        'php_unit_construct',
        'php_unit_strict',
        'phpdoc_order',
        'short_array_syntax',
        'strict',
        'strict_param',
    ))
    ->finder($finder)
;
