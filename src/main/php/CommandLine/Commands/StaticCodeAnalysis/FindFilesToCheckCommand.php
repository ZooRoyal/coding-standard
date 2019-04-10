<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;

class FindFilesToCheckCommand extends Command
{
    /** @var BlacklistFactory */
    private $blacklistFactory;
    /** @var AdaptableFileFinder */
    private $adaptableFileFinder;

    /**
     * FindFilesToCheckCommand constructor.
     *
     * @param BlacklistFactory    $blacklistFactory
     * @param AdaptableFileFinder $adaptableFileFinder
     */
    public function __construct(
        BlacklistFactory $blacklistFactory,
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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $exclusionListInput = $input->getOption('exclusionList');
        $filterInput = $input->getOption('filter');
        $blacklistTokenInput = $input->getOption('blacklist-token');
        $whitelistTokenInput = $input->getOption('whitelist-token');
        $targetBranch = $input->getOption('auto-target') ? null : $input->getOption('target');

        if ($exclusionListInput === true) {
            $result = $this->blacklistFactory->build($blacklistTokenInput);
        } else {
            $result = $this->adaptableFileFinder->findFiles(
                $filterInput,
                $blacklistTokenInput,
                $whitelistTokenInput,
                $targetBranch
            )->getFiles();
        }

        $output->writeln(implode("\n", array_values($result)));
    }

    /**
     * Builds InputDefinition for Command
     *
     * @return InputDefinition
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function buildInputDefinition() : InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption(
                    'target',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Finds Files which have changed since the current branch parted from the target branch '
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
                    'filter',
                    'f',
                    InputOption::VALUE_REQUIRED,
                    'Filters the Filename. For example .php for PHP-Files',
                    ''
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
