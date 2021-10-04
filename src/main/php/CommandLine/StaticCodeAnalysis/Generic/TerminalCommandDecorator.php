<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

interface TerminalCommandDecorator extends EventSubscriberInterface
{
    /**
     * This method decorates the TerminalCommand contained in the generic event. It will read information from the
     * surrounding infrastructure or the command input.
     */
    public function decorate(GenericEvent $genericEvent): void;
}
