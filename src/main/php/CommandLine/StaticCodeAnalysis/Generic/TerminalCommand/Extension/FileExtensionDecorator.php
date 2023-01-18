<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class FileExtensionDecorator extends TerminalCommandDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();

        if (!$terminalCommand instanceof FileExtensionTerminalCommand) {
            return;
        }

        $output = $event->getOutput();
        $extensions = $event->getAllowedFileEndings();

        $output->writeln(
            '<info>Command will only check files with following extensions</info>',
            OutputInterface::VERBOSITY_VERBOSE,
        );
        $output->writeln(implode(' ', $extensions) . PHP_EOL, OutputInterface::VERBOSITY_VERBOSE);

        $terminalCommand->addAllowedFileExtensions($extensions);
    }
}
