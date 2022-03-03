<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ApplicationLifeCycle;

use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose\VerboseDecorator;

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
            FileExtensionDecorator::class,
            FixDecorator::class,
            TargetDecorator::class,
            VerboseDecorator::class,
            MultiprocessDecorator::class,
        ];
    /**
     * EventDispatcherFactory constructor.
     */
    public function __construct(private Container $container)
    {
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
