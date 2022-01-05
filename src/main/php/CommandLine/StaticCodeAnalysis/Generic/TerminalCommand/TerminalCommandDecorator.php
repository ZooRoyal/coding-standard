<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class TerminalCommandDecorator implements EventSubscriberInterface
{
    public const EVENT_DECORATE_TERMINAL_COMMAND = 'eventDecorateTerminalCommand';
    public const KEY_EXCLUSION_LIST_TOKEN = 'exclusionListToken';
    public const KEY_ALLOWED_FILE_ENDINGS = 'allowedFileEndings';
    public const KEY_INPUT = 'input';
    public const KEY_OUTPUT = 'output';

    /**
     * This method decorates the TerminalCommand contained in the generic event. It will read information from the
     * surrounding infrastructure or the command input.
     */
    abstract public function decorate(GenericEvent $genericEvent): void;

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
