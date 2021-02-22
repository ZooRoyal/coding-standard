<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

abstract class AbstractFixableToolCommand extends Command
{
    /** @var FixerSupportInterface|ToolAdapterInterface */
    protected $toolAdapter;

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $targetBranch = $input->getOption('auto-target') ? null : $input->getOption('target');
        $fixMode = $input->getOption('fix');

        if ($fixMode) {
            $this->toolAdapter->fixViolations($targetBranch);
        }

        $exitCode = $this->toolAdapter->writeViolationsToOutput($targetBranch);

        return $exitCode;
    }

    /**
     * Builds InputDefinition for Command.
     *
     * @return InputDefinition
     */
    protected function buildInputDefinition() : InputDefinition
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
                    'fix',
                    'f',
                    InputOption::VALUE_NONE,
                    'Runs tool to try to fix violations automagically.'
                ),
            ]
        );
    }
}
