<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;

class FindFilesToCheckCommand extends Command
{
    /** @var ExclusionListFactory */
    private $blacklistFactory;
    /** @var AdaptableFileFinder */
    private $adaptableFileFinder;

    /**
     * FindFilesToCheckCommand constructor.
     *
     * @param ExclusionListFactory $blacklistFactory
     * @param AdaptableFileFinder  $adaptableFileFinder
     */
    public function __construct(
        ExclusionListFactory $blacklistFactory,
        AdaptableFileFinder $adaptableFileFinder
    ) {
        $this->blacklistFactory = $blacklistFactory;
        $this->adaptableFileFinder = $adaptableFileFinder;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('find-files');
        $this->setDescription('Finds files for code style checks.');
        $this->setHelp('This tool finds files, which should be considered for code style checks.');
        $this->setDefinition($this->buildInputDefinition());
    }


    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $exclusionListInput = $input->getOption('exclusionList');
        $allowedFileEndings = $input->getOption('allowed-file-endings');
        $blacklistTokenInput = $input->getOption('blacklist-token');
        $whitelistTokenInput = $input->getOption('whitelist-token');
        $targetBranch = $input->getOption('auto-target') ? null : $input->getOption('target');

        if ($exclusionListInput === true) {
            $result = $this->blacklistFactory->build($blacklistTokenInput);
        } else {
            $result = $this->adaptableFileFinder->findFiles(
                $allowedFileEndings,
                $blacklistTokenInput,
                $whitelistTokenInput,
                $targetBranch
            )->getFiles();
        }

        $output->writeln(implode("\n", array_values($result)));
        return 0;
    }

    /**
     * Builds InputDefinition for Command
     *
     * @return InputDefinition
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
                    false
                ),
                new InputOption(
                    'auto-target',
                    'a',
                    InputOption::VALUE_NONE,
                    'Finds Files which have changed since the current branch parted from the parent branch '
                    . 'only. It tries to find the parent branch by automagic.'
                ),
                new InputOption(
                    'blacklist-token',
                    'b',
                    InputOption::VALUE_REQUIRED,
                    'Name of the file which triggers the exclusion of the path',
                    ''
                ),
                new InputOption(
                    'whitelist-token',
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
}
