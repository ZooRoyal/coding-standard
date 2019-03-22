<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AllToolsCommand extends AbstractFixableToolCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:all');
        $this->setDescription('Run all static code analysis tools.');
        $this->setHelp('This tool executes all static code analysis tools on files of this Project. '
            . 'It ignores files which are in directories with a .dont<toolshortcut> file. Subdirectories are ignored too.');
        $this->setDefinition($this->buildInputDefinition());
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('All SCA-Commands will be executed.', OutputInterface::OUTPUT_NORMAL);

        $resultingExitCode = 0;
        $inputOptions = $input->getOptions();

        /** @var Command[] $commands */
        $commands = $this->getApplication()->all('sca');

        foreach ($commands as $command) {
            if ($command->getName() === 'sca:all') {
                continue;
            }
            $arguments = [];
            $commandOptions = $command->getDefinition()->getOptions();
            $intersections = array_keys(array_intersect_key($inputOptions, $commandOptions));

            foreach ($intersections as $intersectionName) {
                $arguments['--' . $intersectionName] = $input->getOption($intersectionName);
            }

            $commandInput = new ArrayInput($arguments);
            $exitCode = $command->run($commandInput, $output);

            if ($exitCode !== 0) {
                $output->writeln('Exitcode:' . $exitCode, OutputInterface::OUTPUT_NORMAL);
            }

            $resultingExitCode = $exitCode !== 0 && $resultingExitCode === 0 ? $exitCode : $resultingExitCode;
        }

        return $resultingExitCode;
    }
}
