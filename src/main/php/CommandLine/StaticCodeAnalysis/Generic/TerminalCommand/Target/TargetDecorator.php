<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Git\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\Git\ParentBranchGuesser;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

class TargetDecorator extends TerminalCommandDecorator
{
    private AdaptableFileFinder $adaptableFileFinder;
    private ParentBranchGuesser $parentBranchGuesser;

    public function __construct(
        AdaptableFileFinder $adaptableFileFinder,
        ParentBranchGuesser $parentBranchGuesser
    ) {
        $this->adaptableFileFinder = $adaptableFileFinder;
        $this->parentBranchGuesser = $parentBranchGuesser;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof TargetTerminalCommand) {
            return;
        }

        $input = $genericEvent->getArgument(TerminalCommandDecorator::KEY_INPUT);
        $output = $genericEvent->getArgument(TerminalCommandDecorator::KEY_OUTPUT);

        $isAutoTarget = $input->getOption(TargetableInputFacet::OPTION_AUTO_TARGET);
        $target = $input->getOption(TargetableInputFacet::OPTION_TARGET);

        if (!$isAutoTarget && !$target) {
            return;
        }

        $targetBranch = $isAutoTarget
            ? $this->parentBranchGuesser->guessParentBranchAsCommitHash()
            : $target;

        $allowedFileEndings = $genericEvent->getArgument(TerminalCommandDecorator::KEY_ALLOWED_FILE_ENDINGS);
        $exclusionListToken = $genericEvent->getArgument(TerminalCommandDecorator::KEY_EXCLUSION_LIST_TOKEN);

        $gitChangeSet = $this->adaptableFileFinder->findFiles(
            $allowedFileEndings,
            $exclusionListToken,
            '',
            $targetBranch
        );

        $targets = $gitChangeSet->getFiles();
        $this->writeOutput($output, $gitChangeSet, $targets);

        $terminalCommand->addTargets($targets);
    }

    /**
     * This method writes the targeted files list to the verbose output. It informs the user about the targeted git
     * branch.
     *
     * @param array<EnhancedFileInfo> $targets
     */
    private function writeOutput(OutputInterface $output, GitChangeSet $gitChangeSet, array $targets): void
    {
        $output->writeln(
            '<info>Checking diff to ' . $gitChangeSet->getCommitHash() . '</info>',
            OutputInterface::VERBOSITY_NORMAL
        );

        $output->writeln(
            '<info>Following files will be checked</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );

        foreach ($targets as $target) {
            $output->writeln($target->getRealPath(), OutputInterface::VERBOSITY_VERBOSE);
        }
        $output->writeln('');
    }
}
