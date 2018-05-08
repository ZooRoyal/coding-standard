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
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSESLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;

class JSStyleLintCommand extends Command
{
    /** @var JSStyleLintAdapter */
    private $toolAdapter;

    /**
     * JSStyleLintCommand constructor.
     *
     * @param JSStyleLintAdapter $toolAdapter
     */
    public function __construct(JSStyleLintAdapter $toolAdapter)
    {
        parent::__construct();
        $this->toolAdapter = $toolAdapter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('stylelint');
        $this->setDescription('Run StyleLint on Less files.');
        $this->setHelp('This tool executes STYLELINT on a certain set of Less files of this Project.'
        . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'target',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Lints Less-Files which have changed since the current branch parted from the target path only.',
                        ''
                    ),
                    new InputOption(
                        'fix',
                        'f',
                        InputOption::VALUE_NONE,
                        'Fix all changed Less Files'
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
