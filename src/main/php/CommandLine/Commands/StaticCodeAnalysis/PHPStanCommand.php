<?php


namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPStanAdapter;

class PHPStanCommand extends Command
{
    private PHPStanAdapter $toolAdapter;

    /**
     * PHPCodeSnifferCommand constructor.
     *
     * @param PHPStanAdapter $toolAdapter
     */
    public function __construct(
        PHPStanAdapter $toolAdapter
    ) {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('sca:stan');
        $this->setDescription('Run PHPStan on PHP files.');
        $this->setHelp('This tool executes PHPStan on a certain set of PHP files of this project.'
                       . 'It ignores files which are in directories with a .dontStanPHP file. Subdirectories are ignored too.');
        $this->setDefinition($this->getInputDefinition());
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $targetBranch = $input->getOption('auto-target') ? null : $input->getOption('target');

        return $this->toolAdapter->writeViolationsToOutput($targetBranch);
    }

     /**
      * {@inheritdoc}
      */
    protected function getInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                'target',
                't',
                InputOption::VALUE_REQUIRED,
                'Finds files which have changed since the current branch parted from the target branch only. 
                The value has to be a commit-ish.',
                false
            ),
            new InputOption(
                'auto-target',
                'a',
                InputOption::VALUE_NONE,
                'Finds files which have changed since the current branch parted from the parent branch only. 
                It tries to find the parent branch by automagic.'
            ),
        ]);
    }


}
