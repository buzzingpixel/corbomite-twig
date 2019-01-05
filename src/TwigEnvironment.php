<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigEnvironment extends Environment
{
    public function getLoader(): FilesystemLoader
    {
        return parent::getLoader();
    }
}
