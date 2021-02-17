<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\GitCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\TerminalCommandPreconditionChecker;

/**
 * Class EventDispatcherFactory
 * This class builds the EventDispatcher used in the coding-standard cli. This class is meant to be used as a PHP-DI
 * factory.
 */
class EventDispatcherFactory
{
    private Container $container;
    /** @var array<string> */
    private array $subscribers = [GitCommandPreconditionChecker::class, TerminalCommandPreconditionChecker::class];

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
     *
     * @return EventDispatcher
     */
    public function build(): EventDispatcher
    {
        $eventDispatcher = new EventDispatcher();

        foreach ($this->subscribers as $subscriber) {
            $eventDispatcher->addSubscriber($this->container->get($subscriber));
        }

        return $eventDispatcher;
    }
}
