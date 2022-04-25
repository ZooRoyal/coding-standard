<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class TerminalCommandDecorator implements EventSubscriberInterface
{
    public const EVENT_DECORATE_TERMINAL_COMMAND = 'eventDecorateTerminalCommand';

    /**
     * This method decorates the TerminalCommand contained in the generic event. It will read information from the
     * surrounding infrastructure or the command input.
     */
    abstract public function decorate(DecorateEvent $event): void;

    /**
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    final public static function getSubscribedEvents(): array
    {
        return [self::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }
}
