<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use corbomite\di\Di;
use corbomite\twig\TwigEnvironment;

// define('APP_BASE_PATH', __DIR__);

require_once 'vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
$whoops->register();

require_once __DIR__ . '/devDumper.php';

dd(Di::get(TwigEnvironment::class));
