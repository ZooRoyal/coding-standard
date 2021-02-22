<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPMessDetectorAdapter;

class PHPMessDetectorCommand extends Command
{
    /** @var PHPMessDetectorAdapter */
    private $toolAdapter;

    /**
     * PHPMessDetectorCommand constructor.
     *
     * @param PHPMessDetectorAdapter $toolAdapter
     */
    public function __construct(PHPMessDetectorAdapter $toolAdapter)
    {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:mess-detect');
        $this->setDescription('Run PHP-MD on PHP files.');
        $this->setHelp(
            'This tool executes PHP-MD on a certain set of PHP files of this project. It ignores files which are in '
            . 'directories with a .dontMessDetectPHP file. Subdirectories are ignored too.'
        );
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'target',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Finds files which have changed since the current branch parted from the target branch '
                        . 'only. The value has to be a commit-ish.',
                        false
                    ),
                    new InputOption(
                        'auto-target',
                        'a',
                        InputOption::VALUE_NONE,
                        'Finds files which have changed since the current branch parted from the parent branch '
                        . 'only. It tries to find the parent branch by automagic.'
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
        $targetBranch = $input->getOption('auto-target') ? null : $input->getOption('target');

        $exitCode = $this->toolAdapter->writeViolationsToOutput($targetBranch);

        return $exitCode;
    }
}
