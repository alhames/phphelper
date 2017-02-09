<?php

/**
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 */

$header = <<<EOF
This file is part of the PHP Helper package.

(c) Pavel Logachev <alhames@mail.ru>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unreachable_default_argument_value' => false,
        'heredoc_to_nowdoc' => false,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'pre_increment' => false,
        'header_comment' => ['header' => $header],
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->files()
            ->name('*.php')
            ->in([
                __DIR__.'/src',
                __DIR__.'/tests'
            ])
    )
;
