<?php
declare(strict_types=1);

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
