<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\twig\factories;

use corbomite\di\Di;
use corbomite\twig\TwigEnvironment;

class TwigEnvironmentFactory
{
    public function make(): TwigEnvironment
    {
        return Di::get(TwigEnvironment::class);
    }

    public function getTemplateDirectories(): array
    {
        return Di::get('CorbomiteTwig.TemplateDirectories');
    }
}
