<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Decorators;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\ParentBranchGuesser;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TargetableTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class TargetDecorator implements TerminalCommandDecorator
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

        if (!$terminalCommand instanceof TargetableTerminalCommand) {
            return;
        }

        $input = $genericEvent->getArgument(AbstractToolCommand::KEY_INPUT);
        $output = $genericEvent->getArgument(AbstractToolCommand::KEY_OUTPUT);

        $isAutoTarget = $input->getOption(TargetableInputFacet::OPTION_AUTO_TARGET);
        $target = $input->getOption(TargetableInputFacet::OPTION_TARGET);

        if (!$isAutoTarget && !$target) {
            return;
        }

        $targetBranch = $isAutoTarget
            ? $this->parentBranchGuesser->guessParentBranchAsCommitHash()
            : $target;

        $allowedFileEndings = $genericEvent->getArgument(AbstractToolCommand::KEY_ALLOWED_FILE_ENDINGS);
        $exclusionListToken = $genericEvent->getArgument(AbstractToolCommand::KEY_EXCLUSION_LIST_TOKEN);

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
     * {@inheritDoc}
     *
     * @return array<string,array<int,int|string>>
     */
    public static function getSubscribedEvents()
    {
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
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
