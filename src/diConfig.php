<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Composer\Autoload\ClassLoader;
use corbomite\twig\TwigEnvironment;
use Psr\Container\ContainerInterface;
use buzzingpixel\minify\interfaces\MinifyApiInterface;
use corbomite\configcollector\Factory as CollectorFactory;

return [
    'CorbomiteTwig.AppBasePath' => function () {
        if (defined('APP_BASE_PATH')) {
            return APP_BASE_PATH;
        }

        $reflection = new ReflectionClass(ClassLoader::class);

        return dirname($reflection->getFileName(), 3);
    },
    'CorbomiteTwig.TemplateDirectories' => function (ContainerInterface $di) {
        $dirs = [];

        $collector = CollectorFactory::collector();

        /** @var string $appBasePath */
        $appBasePath = $di->get('CorbomiteTwig.AppBasePath');

        $item = $collector->getExtraKeyFromPath($appBasePath, 'twigTemplatesDirectories');
        $item = \is_array($item) ? $item : [];

        foreach ($item as $k => $v) {
            $dirs[$k] = $appBasePath . DIRECTORY_SEPARATOR . $v;
        }

        $vendorIterator = CollectorFactory::directoryIterator(
            $appBasePath . DIRECTORY_SEPARATOR . 'vendor'
        );

        foreach ($vendorIterator as $fileInfo) {
            if ($fileInfo->isDot() || ! $fileInfo->isDir()) {
                continue;
            }

            $providerIterator = CollectorFactory::directoryIterator(
                $fileInfo->getPathname()
            );

            foreach ($providerIterator as $providerFileInfo) {
                if ($providerFileInfo->isDot() ||
                    ! $providerFileInfo->isDir()
                ) {
                    continue;
                }

                $item = $collector->getExtraKeyFromPath(
                    $providerFileInfo->getPathname(),
                    'twigTemplatesDirectories'
                );
                $item = \is_array($item) ? $item : [];

                foreach ($item as $k => $v) {
                    $dirs[$k] = $providerFileInfo->getPathname() . DIRECTORY_SEPARATOR . $v;
                }
            }
        }

        return $dirs;
    },
    TwigEnvironment::class => function (ContainerInterface $di) {
        $debug = getenv('DEV_MODE') === 'true';

        $minifyApi = $di->get(MinifyApiInterface::class);

        $twig = new TwigEnvironment(
            new FilesystemLoader(),
            [
                'debug' => $debug,
                'cache' => getenv('TWIG_CACHE_PATH') ?:
                    $di->get('CorbomiteTwig.AppBasePath') . DIRECTORY_SEPARATOR . 'cache',
                'strict_variables' => $debug,
            ],
            $minifyApi
        );

        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }

        $collector = CollectorFactory::collector();

        foreach ($collector->collect('twigGlobalsFilePath') as $key => $val) {
            $twig->addGlobal($key, $val);
        }

        foreach ($collector->getExtraKeyAsArray('twigExtensions') as $twigExtension) {
            $class = null;

            /** @noinspection PhpUnhandledExceptionInspection */
            if ($di->has($twigExtension)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $class = $di->get($twigExtension);
            }

            if (! $class) {
                $class = new $twigExtension();
            }

            $twig->addExtension($class);
        }

        foreach ($di->get('CorbomiteTwig.TemplateDirectories') as $n => $p) {
            $loader = $twig->getLoader();
            $namespace = $n ?: $loader::MAIN_NAMESPACE;
            /** @noinspection PhpUnhandledExceptionInspection */
            $twig->getLoader()->addPath($p, $namespace);
        }

        return $twig;
    },
];
