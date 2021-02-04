<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ApplicationFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\EventDispatcherFactory;
use function DI\factory;
use function DI\get;

return [
    Application::class => factory(ApplicationFactory::class . '::build'),
    EventDispatcherInterface::class => factory(EventDispatcherFactory::class . '::build'),
    InputInterface::class => get(ArgvInput::class),
    OutputInterface::class => get(ConsoleOutput::class),
];
