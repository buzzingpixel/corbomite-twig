<?php
declare(strict_types=1);

use corbomite\twig\TwigEnvironment;
use corbomite\twig\factories\TwigEnvironmentFactory;

return [
    TwigEnvironment::class => function () {
        return (new TwigEnvironmentFactory)->make();
    },
];
