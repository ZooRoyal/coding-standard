<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class ExclusionDecorator extends TerminalCommandDecorator
{
    public function __construct(
        private ExclusionListFactory $exclusionListFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();

        if (!$terminalCommand instanceof ExclusionTerminalCommand) {
            return;
        }

        $output = $event->getOutput();

        $exclusionList = $this->exclusionListFactory
            ->build($event->getExclusionListToken());

        $this->verboseOutputExclusionList($exclusionList, $output);

        $terminalCommand->addExclusions($exclusionList);
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
