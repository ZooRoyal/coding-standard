<?php
namespace Zooroyal\CodingStandard\CommandLine\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;

class PHPCodeSnifferCommand extends Command
{
    /** @var PHPCodeSnifferAdapter */
    private $toolAdapter;

    /**
     * PHPCodeSnifferCommand constructor.
     *
     * @param AllCheckableFileFinder  $allCheckableFileFinder
     * @param DiffCheckableFileFinder $diffCheckableFileFinder
     * @param PHPCodeSnifferAdapter   $toolAdapter
     * @param Environment             $environment
     */
    public function __construct(PHPCodeSnifferAdapter $toolAdapter)
    {
        parent::__construct();
        $this->toolAdapter = $toolAdapter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sniff');
        $this->setDescription('Run PHP-CS on PHP files.');
        $this->setHelp('This tool executes PHP-CS on a certain set of PHP files of this Project. '
            . 'It ignores files which are in directories with a .dontSniffPHP file. Subdirectories are ignored too.');
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
                        'fix',
                        'f',
                        InputOption::VALUE_NONE,
                        'Runs CBF to try to fix violations automagically.'
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
        $fixMode               = $input->getOption('fix');

        if ($fixMode) {
            $this->toolAdapter->fixViolations($targetBranch, $processIsolationInput);
        }

        $exitCode = $this->toolAdapter->writeViolationsToOutput($targetBranch, $processIsolationInput);

        return $exitCode;
    }
}
