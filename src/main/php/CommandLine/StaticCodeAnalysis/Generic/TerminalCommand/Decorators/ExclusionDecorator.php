<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class ExclusionDecorator implements TerminalCommandDecorator
{
    public function __construct(
        private ExclusionListFactory $exclusionListFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof ExcludingTerminalCommand) {
            return;
        }

        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);

        $exclusionList = $this->exclusionListFactory
            ->build($genericEvent->getArgument(AbstractToolCommand::KEY_EXCLUSION_LIST_TOKEN));

        $this->verboseOutputExclusionList($exclusionList, $output);

        $terminalCommand->addExclusions($exclusionList);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }

    /**
     * This method writes the exclusion list to the verbose output.
     *
     * @param array<EnhancedFileInfo> $exclusionList
     */
    private function verboseOutputExclusionList(array $exclusionList, OutputInterface $output): void
    {
        $output->writeln('<info>Following Paths will be excluded</info>', OutputInterface::VERBOSITY_VERBOSE);
        foreach ($exclusionList as $exclusion) {
            $output->writeln($exclusion->getRealPath(), OutputInterface::VERBOSITY_VERBOSE);
        }
        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
    }
}
