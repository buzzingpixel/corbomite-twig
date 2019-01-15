<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\twig;

use Twig\Environment;
use Twig_Error_Loader;
use Twig_Error_Syntax;
use Twig_Error_Runtime;
use Twig_LoaderInterface;
use Twig\Loader\FilesystemLoader;
use buzzingpixel\minify\interfaces\MinifyApiInterface;

class TwigEnvironment extends Environment
{
    private $minifyApi;

    public function __construct(
        Twig_LoaderInterface $loader,
        array $options,
        MinifyApiInterface $minifyApi
    ) {
        parent::__construct($loader, $options);

        $this->minifyApi = $minifyApi;
    }

    public function getLoader(): FilesystemLoader
    {
        return parent::getLoader();
    }

    /**
     * @param string $template
     * @param array $context
     * @param array $minifyOptions
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function renderAndMinify(
        string $template,
        array $context = [],
        array $minifyOptions = []
    ): string {
        return $this->minifyApi->minifyHtml(
            $this->render($template, $context),
            $minifyOptions
        );
    }
}
