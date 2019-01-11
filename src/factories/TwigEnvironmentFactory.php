<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\twig\factories;

use LogicException;
use corbomite\di\Di;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use corbomite\twig\TwigEnvironment;
use corbomite\configcollector\Factory as CollectorFactory;

class TwigEnvironmentFactory
{
    public function make(): TwigEnvironment
    {
        if (! defined('APP_BASE_PATH')) {
            throw new LogicException('APP_BASE_PATH must be defined');
        }

        $debug = getenv('DEV_MODE') === 'true';

        $twig = new TwigEnvironment(new FilesystemLoader(), [
            'debug' => $debug,
            'cache' => getenv('TWIG_CACHE_PATH') ?: APP_BASE_PATH . '/cache',
            'strict_variables' => $debug,
        ]);

        $collector = CollectorFactory::collector();

        foreach ($collector->collect('twigGlobalsFilePath') as $key => $val) {
            $twig->addGlobal($key, $val);
        }

        foreach ($collector->getExtraKeyAsArray('twigExtensions') as $twigExtension) {
            $class = null;

            /** @noinspection PhpUnhandledExceptionInspection */
            if (Di::has($twigExtension)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $class = Di::get($twigExtension);
            }

            if (! $class) {
                $class = new $twigExtension();
            }

            $twig->addExtension($class);
        }

        foreach ($collector->getExtraKeyAsArray('twigTemplatesDirectories') as $n => $p) {
            $loader = $twig->getLoader();
            $namespace = $n ?: $loader::MAIN_NAMESPACE;
            /** @noinspection PhpUnhandledExceptionInspection */
            $twig->getLoader()->addPath($p, $namespace);
        }

        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }
}
