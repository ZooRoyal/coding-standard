<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\Checks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class ForbiddenChangesCommand extends Command
{
    /** @var DiffCheckableFileFinder */
    private $diffCheckableFileFinder;
    /** @var string */
    private $blacklistToken = '.dontChangeFiles';
    /** @var string */
    private $whitelistToken = '.doChangeFiles';
    /** @var Environment */
    private $environment;

    /**
     * ForbiddenChangesCommand constructor.
     *
     * @param DiffCheckableFileFinder $diffCheckableFileFinder
     * @param Environment $environment
     */
    public function __construct(
        DiffCheckableFileFinder $diffCheckableFileFinder,
        Environment $environment
    ) {
        parent::__construct();
        $this->diffCheckableFileFinder = $diffCheckableFileFinder;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('checks:forbidden-changes');
        $this->setDescription('Checks for unwanted code changes.');
        $this->setHelp('This tool checks if there where changes made to files. If a parent directory contains a '
            . ' ' . $this->blacklistToken . ' file the tools will report the violation. Changes in subdirectories of a '
            . 'marked directory may be allowed by placing a ' . $this->whitelistToken . ' file in the subdirectory.'
            . ' Use parameter to determine if this should be handled as Warning or not.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'warn',
                        'w',
                        InputOption::VALUE_NONE,
                        'Exists with exit code 0 event if violations are found'
                    ),
                    new InputOption(
                        'target',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'Finds Files which have changed since the current branch parted from the target branch '
                        . 'only. If no branch is set the tool tries to find the parent branch by automagic. '
                        . 'The Value, if set, has to be a commit-ish.',
                        null
                    ),
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $warning = $input->getOption('warn');
        $targetBranch = $input->getOption('target') ?? $this->environment->guessParentBranchAsCommitHash();

        $output->writeln('Checking diff to ' . $targetBranch . ' for forbidden changes.');

        $wrongfullyChangedFiles = $this->diffCheckableFileFinder
            ->findFiles('', $this->whitelistToken, $this->blacklistToken, $targetBranch)->getFiles();

        $this->publishFindingsToUser($output, $wrongfullyChangedFiles);

        return empty($wrongfullyChangedFiles) || $warning ? 0 : 100;
    }

    /**
     * Communicates the result to the User.
     *
     * @param OutputInterface $output
     * @param string[] $wrongfullyChangedFiles
     */
    private function publishFindingsToUser(OutputInterface $output, array $wrongfullyChangedFiles)
    {
        if (empty($wrongfullyChangedFiles)) {
            $output->writeln('All good!');
        } else {
            $output->writeln(
                'The following files violate change constraints: ' . PHP_EOL
                . implode(PHP_EOL, $wrongfullyChangedFiles)
            );
        }
    }

}
