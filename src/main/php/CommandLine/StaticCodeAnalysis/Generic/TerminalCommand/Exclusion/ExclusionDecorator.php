<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;

class ExclusionDecorator extends TerminalCommandDecorator
{
    private ExclusionListFactory $exclusionListFactory;

    public function __construct(
        ExclusionListFactory $exclusionListFactory
    ) {
        $this->exclusionListFactory = $exclusionListFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof ExclusionTerminalCommand) {
            return;
        }

        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);

        $exclusionList = $this->exclusionListFactory
            ->build($genericEvent->getArgument(TerminalCommandDecorator::KEY_EXCLUSION_LIST_TOKEN));

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
