<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class FileExtensionDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof FileExtensionTerminalCommand) {
            return;
        }

        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);
        $extensions = $genericEvent->getArgument(TerminalCommandDecorator::KEY_ALLOWED_FILE_ENDINGS);

        $output->writeln(
            '<info>Command will only check files with following extensions</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );
        $output->writeln(implode(' ', $extensions) . PHP_EOL, OutputInterface::VERBOSITY_VERBOSE);

        $terminalCommand->addAllowedFileExtensions($extensions);
    }
}
