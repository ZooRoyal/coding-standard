<?php
namespace Zooroyal\CodingStandard\CommandLine\Commands;

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
        $this->setName('mess-detect');
        $this->setDescription('Run PHP-MD on PHP files.');
        $this->setHelp('This tool executes PHP-MD on a certain set of PHP files of this Project. It ignores files which are in '
            . 'directories with a .dontMessDetectPHP file. Subdirectories are ignored too.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'target',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Finds PHP-Files which have changed since the current branch parted from the target branch only.',
                        ''
                    ),
                    new InputOption(
                        'process-isolation',
                        'p',
                        InputOption::VALUE_NONE,
                        'Runs all checks in separate processes. Slow but not as resource hungry.'
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
        $targetBranch          = $input->getOption('target');
        $processIsolationInput = $input->getOption('process-isolation');

        $exitCode = $this->toolAdapter->writeViolationsToOutput($targetBranch, $processIsolationInput);

        return $exitCode;
    }
}
