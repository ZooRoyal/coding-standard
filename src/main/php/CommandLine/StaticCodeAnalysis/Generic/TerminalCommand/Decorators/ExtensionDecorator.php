<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;

class ExtensionDecorator implements TerminalCommandDecorator
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

        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);
        $extensions = $genericEvent->getArgument(AbstractToolCommand::KEY_ALLOWED_FILE_ENDINGS);

        $output->writeln(
            '<info>Command will only check files with following extensions</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );
        $output->writeln(implode(' ', $extensions) . PHP_EOL, OutputInterface::VERBOSITY_VERBOSE);

        $terminalCommand->addAllowedFileExtensions($extensions);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    public static function getSubscribedEvents()
    {
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }
}
