<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\GitCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\TerminalCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\ExclusionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\ExtensionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\FixDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\MultiprocessDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\TargetDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators\VerbosityDecorator;

/**
 * Class EventDispatcherFactory
 * This class builds the EventDispatcher used in the coding-standard cli. This class is meant to be used as a PHP-DI
 * factory.
 */
class EventDispatcherFactory
{
    /** @var array<string> */
    private const SUBSCRIBERS
        = [
            GitCommandPreconditionChecker::class,
            TerminalCommandPreconditionChecker::class,
            ExclusionDecorator::class,
            ExtensionDecorator::class,
            FixDecorator::class,
            TargetDecorator::class,
            VerbosityDecorator::class,
            MultiprocessDecorator::class,
        ];
    private Container $container;

    /**
     * EventDispatcherFactory constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * This method returns the EventDispatcher prehooked with all EventSubscribers used in coding-standard cli.
     */
    public function build(): EventDispatcher
    {
        $eventDispatcher = new EventDispatcher();

        foreach (self::SUBSCRIBERS as $subscriber) {
            $eventDispatcher->addSubscriber($this->container->get($subscriber));
        }

        return $eventDispatcher;
    }
}
