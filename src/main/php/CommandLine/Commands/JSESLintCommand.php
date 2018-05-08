<?php
namespace Zooroyal\CodingStandard\CommandLine\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSESLintAdapter;

class JSESLintCommand extends Command
{
    /** @var JSESLintAdapter */
    private $toolAdapter;

    /**
     * JSESLintCommand constructor.
     *
     * @param JSESLintAdapter $toolAdapter
     */
    public function __construct(JSESLintAdapter $toolAdapter)
    {
        parent::__construct();
        $this->toolAdapter = $toolAdapter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('eslint');
        $this->setDescription('Run ESLint on JS files.');
        $this->setHelp('This tool executes ESLINT on a certain set of JS files of this Project.'
            . 'Add a .dontSniffJS file to <JS-DIRECTORIES> that should be ignored.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'target',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Finds JS-Files which have changed since the current branch parted from the target branch only.',
                        ''
                    ),
                    new InputOption(
                        'fix',
                        'f',
                        InputOption::VALUE_NONE,
                        'Runs EsLint to try to fix violations automagically.'
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
