<?php
declare(strict_types=1);

namespace corbomite\twig\factories;

use Exception;
use corbomite\di\Di;
use Composer\Console\Application;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use corbomite\twig\TwigEnvironment;
use Composer\Package\CompletePackage;

class TwigEnvironmentFactory
{
    public function make(): TwigEnvironment
    {
        if (! defined('APP_BASE_PATH')) {
            throw new Exception('APP_BASE_PATH must be defined');
        }

        $debug = getenv('DEV_MODE') === 'true';

        $twig = new TwigEnvironment(new FilesystemLoader(), [
            'debug' => $debug,
            'cache' => getenv('TWIG_CACHE_PATH') ?: APP_BASE_PATH . '/cache',
            'strict_variables' => $debug,
        ]);

        foreach ($this->getComposerExtras() as $extra) {
            if (isset($extra['twigGlobalsFilePath'])) {
                $globals = include $extra['twigGlobalsFilePath'];
                foreach ($globals as $key => $val) {
                    $twig->addGlobal($key, $val);
                }
            }

            if (isset($extra['twigExtensions'])) {
                foreach ($extra['twigExtensions'] as $twigExtension) {
                    $class = null;

                    if (Di::has($twigExtension)) {
                        $class = Di::get($twigExtension);
                    }

                    if (! $class) {
                        $class = new $twigExtension();
                    }

                    $twig->addExtension($class);
                }
            }

            if (isset($extra['twigTemplatesDirectories'])) {
                foreach ($extra['twigTemplatesDirectories'] as $n => $p) {
                    $loader = $twig->getLoader();
                    $namespace = $n ?: $loader::MAIN_NAMESPACE;
                    $twig->getLoader()->addPath($p, $namespace);
                }
            }
        }

        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }

    private function getComposerExtras(): array
    {
        $extras = [];

        $appJsonPath = APP_BASE_PATH . '/composer.json';

        if (file_exists($appJsonPath)) {
            $appJson = json_decode(file_get_contents($appJsonPath), true);

            if (isset($appJson['extra'])) {
                $extra = $appJson['extra'];
                $send = [];

                if (isset($extra['twigGlobalsFilePath'])) {
                    $send['twigGlobalsFilePath'] = APP_BASE_PATH .
                        '/' .
                        $extra['twigGlobalsFilePath'];
                }

                if (isset($extra['twigExtensions'])) {
                    $send['twigExtensions'] = $extra['twigExtensions'];
                }

                if (isset($extra['twigTemplatesDirectories'])) {
                    $dirs = [];

                    foreach ($extra['twigTemplatesDirectories'] as $k => $v) {
                        $dirs[$k] = APP_BASE_PATH . '/' . $v;
                    }

                    if ($dirs) {
                        $send['twigTemplatesDirectories'] = $dirs;
                    }
                }

                if ($send) {
                    $extras[] = $send;
                }
            }
        }

        foreach ($this->getComposerPackages() as $package) {
            if (! ($extra = $package->getExtra())) {
                continue;
            }

            $send = [];

            if (isset($extra['twigGlobalsFilePath'])) {
                $send['twigGlobalsFilePath'] = APP_BASE_PATH .
                    '/vendor/' .
                    $package->getName() .
                    '/' .
                    $extra['twigGlobalsFilePath'];
            }

            if (isset($extra['twigExtensions'])) {
                $send['twigExtensions'] = $extra['twigExtensions'];
            }

            if (isset($extra['twigTemplatesDirectories'])) {
                $dirs = [];

                foreach ($extra['twigTemplatesDirectories'] as $k => $v) {
                    $dirs[$k] = APP_BASE_PATH .
                        '/vendor/' .
                        $package->getName() .
                        '/' .
                        $v;
                }

                if ($dirs) {
                    $send['twigTemplatesDirectories'] = $dirs;
                }
            }

            if ($send) {
                $extras[] = $send;
            }
        }

        return $extras;
    }

    /**
     * @return CompletePackage[]
     */
    private function getComposerPackages(): array
    {
        // Edge case and weirdness with composer
        getenv('HOME') || putenv('HOME=' . __DIR__);

        $oldCwd = getcwd();

        chdir(APP_BASE_PATH);

        $composerApp = new Application();

        /** @noinspection PhpUnhandledExceptionInspection */
        $composer = $composerApp->getComposer();
        $repositoryManager = $composer->getRepositoryManager();
        $installedFilesystemRepository = $repositoryManager->getLocalRepository();
        $packages = $installedFilesystemRepository->getCanonicalPackages();

        chdir($oldCwd);

        return $packages;
    }
}
