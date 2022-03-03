<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinder\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\ParentBranchGuesser;

class FindFilesToCheckCommand extends Command
{
    public function __construct(
        private ExclusionListFactory $blacklistFactory,
        private AdaptableFileFinder $adaptableFileFinder,
        private ParentBranchGuesser $parentBranchGuesser,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setName('find-files');
        $this->setDescription('Finds files for code style checks.');
        $this->setHelp('This tool finds files, which should be considered for code style checks.');
        $this->setDefinition($this->buildInputDefinition());
    }

    /**
     * Builds InputDefinition for Command
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function buildInputDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption(
                    'target',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Finds files which have changed since the current branch parted from the target branch '
                    . 'only. The Value has to be a commit-ish.',
                    null
                ),
                new InputOption(
                    'auto-target',
                    'a',
                    InputOption::VALUE_NONE,
                    'Finds Files which have changed since the current branch parted from the parent branch '
                    . 'only. It tries to find the parent branch by automagic.'
                ),
                new InputOption(
                    'exclusionlist-token',
                    'b',
                    InputOption::VALUE_REQUIRED,
                    'Name of the file which triggers the exclusion of the path',
                    ''
                ),
                new InputOption(
                    'inclusionlist-token',
                    'w',
                    InputOption::VALUE_REQUIRED,
                    'Name of the file which triggers the inclusion of the path',
                    ''
                ),
                new InputOption(
                    'allowed-file-endings',
                    'f',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Only list files with appropriate file endings. For example .php for PHP-Files. '
                    . 'You may give multiple',
                    []
                ),
                new InputOption(
                    'exclusionList',
                    'e',
                    InputOption::VALUE_NONE,
                    'Gathers list of directories which should be excluded'
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $exclusionListInput = $input->getOption('exclusionList');
        $allowedFileEndings = $input->getOption('allowed-file-endings');
        $exclusionListTokenInput = $input->getOption('exclusionlist-token');
        $inclusionListTokenInput = $input->getOption('inclusionlist-token');
        $targetBranch = $input->getOption('auto-target')
            ? $this->parentBranchGuesser->guessParentBranchAsCommitHash()
            : $input->getOption('target');

        if ($exclusionListInput === true) {
            $exclusionList = $this->exclusionListFactory->build($exclusionListTokenInput);
            $result = array_map(static fn(EnhancedFileInfo $file) => $file->getRelativePathname() . '/', $exclusionList);
        } else {
            $foundFiles = $this->adaptableFileFinder->findFiles(
                $allowedFileEndings,
                $exclusionListTokenInput,
                $inclusionListTokenInput,
                $targetBranch
            )->getFiles();
            $result = array_map(static fn(EnhancedFileInfo $file): string => $file->getRelativePathname(), $foundFiles);
        }

        $output->writeln(implode(PHP_EOL, $result));
        return 0;
    }
}
