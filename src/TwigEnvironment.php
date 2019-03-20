<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\twig;

use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Loader\LoaderInterface;
use Twig\Loader\FilesystemLoader;
use buzzingpixel\minify\interfaces\MinifyApiInterface;

class TwigEnvironment extends Environment
{
    private $minifyApi;

    public function __construct(
        LoaderInterface $loader,
        array $options,
        MinifyApiInterface $minifyApi
    ) {
        parent::__construct($loader, $options);

        $this->minifyApi = $minifyApi;
    }

    public function getLoader(): FilesystemLoader
    {
        /** @var FilesystemLoader $fileSystemLoader */
        $fileSystemLoader = parent::getLoader();
        return $fileSystemLoader;
    }

    /**
     * @param string $template
     * @param array $context
     * @param array $minifyOptions
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
