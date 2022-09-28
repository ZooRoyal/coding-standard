<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\FileFinder\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
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
    public function decorate(DecorateEvent $event): void
    {
        $terminalCommand = $event->getTerminalCommand();
        if (!$terminalCommand instanceof TargetTerminalCommand) {
            return;
        }

        $targetBranch = null;
        $input = $event->getInput();
        $allowedFileEndings = $event->getAllowedFileEndings();
        $exclusionListToken = $event->getExclusionListToken();
        $hasTargetOption = $input->hasOption(TargetableInputFacet::OPTION_AUTO_TARGET)
            || $input->hasOption(TargetableInputFacet::OPTION_TARGET);

        if ($hasTargetOption) {
            $isAutoTarget = $input->getOption(TargetableInputFacet::OPTION_AUTO_TARGET);
            $target = $input->getOption(TargetableInputFacet::OPTION_TARGET);

            if (!$isAutoTarget && !$target) {
                return;
            }

            $targetBranch = $isAutoTarget
                ? $this->parentBranchGuesser->guessParentBranchAsCommitHash()
                : $target;
        }

        $gitChangeSet = $this->adaptableFileFinder->findFiles(
            $allowedFileEndings,
            $exclusionListToken,
            '',
            $targetBranch
        );

        $targets = $gitChangeSet->getFiles();
        $this->writeOutput($event->getOutput(), $gitChangeSet, $targets);
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
        $commitHash = $gitChangeSet->getCommitHash();

        if ($commitHash === '') {
            $message = '<info>No Target was specified so all applicable files are targeted</info>';
        } else {
            $message = '<info>Checking diff to ' . $gitChangeSet->getCommitHash() . '</info>';
        }

        $output->writeln(
            $message,
            OutputInterface::VERBOSITY_NORMAL
        );

        $output->writeln(
            '<info>Following files will be targeted</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );

        foreach ($targets as $target) {
            $output->writeln($target->getRealPath(), OutputInterface::VERBOSITY_VERBOSE);
        }
        $output->writeln('');
    }
}
