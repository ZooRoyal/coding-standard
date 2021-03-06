<?php

use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ApplicationFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\EventDispatcherFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\GitPathsExcluder;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\StaticExcluder;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\TokenExcluder;
use function DI\factory;
use function DI\get;

return [
    Application::class => factory(ApplicationFactory::class . '::build'),
    EventDispatcherInterface::class => factory(EventDispatcherFactory::class . '::build'),
    InputInterface::class => get(ArgvInput::class),
    OutputInterface::class => get(ConsoleOutput::class),

    'excluders' => factory(
        static function (Container $container) {
            $result[] = $container->get(GitPathsExcluder::class);
            $result[] = $container->get(StaticExcluder::class);
            $result[] = $container->get(TokenExcluder::class);
            return $result;
        }
    ),
];
