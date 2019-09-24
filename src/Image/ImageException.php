<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper\Image;

/**
 * Class ImageException.
 */
class ImageException extends \RuntimeException
{
    const ERR_SIZE = 1;
    const ERR_TYPE = 2;
    const ERR_PATH = 3;
    const ERR_READ = 4;
    const ERR_WRITE = 5;
    const ERR_RESOLUTION = 6;
}
