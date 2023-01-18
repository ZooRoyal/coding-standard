<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

class AllToolsCommand extends Command
{
    /** @var array<InputOption> */
    private array $injectedOptions;

    public function __construct(
        FixableInputFacet $fixableFacet,
        TargetableInputFacet $targetableFacet,
        ?string $name = null,
    ) {
        $this->injectedOptions = array_merge(
            $fixableFacet->getInputDefinition()->getOptions(),
            $targetableFacet->getInputDefinition()->getOptions(),
        );
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('sca:all');
        $this->setDescription('Run all static code analysis tools.');
        $this->setHelp(
            'This tool executes all static code analysis tools on files of this project. '
            . 'It ignores files which are in directories with a .dont<toolshortcut> file. Subdirectories are ignored too.',
        );
        $this->getDefinition()->setOptions($this->injectedOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>All SCA-Commands will be executed.</comment>', OutputInterface::OUTPUT_NORMAL);

        $unfilteredInputOptions = $input->getOptions();
        $defaultOptionValues = $this->getDefinition()->getOptionDefaults();

        $inputOptions = array_filter(
            $unfilteredInputOptions,
            static fn ($value, $key) => !array_key_exists($key, $defaultOptionValues) || $defaultOptionValues[$key] !== $value,
            ARRAY_FILTER_USE_BOTH,
        );

        $resultingExitCode = $this->executeSubcommands($inputOptions, $input, $output);

        return $resultingExitCode;
    }

    /**
     * This method calls execute of all commands with the prefix sca excluding sca:all.
     *
     * @param array<array|bool|float|int|string|null> $inputOptions
     */
    private function executeSubcommands(array $inputOptions, InputInterface $input, OutputInterface $output): int
    {
        $commands = $this->getApplication()->all('sca');

        $resultingExitCode = 0;
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
                $output->writeln('<error>Exitcode:' . $exitCode . '</error>', OutputInterface::OUTPUT_NORMAL);
            }

            $resultingExitCode = $exitCode !== 0 && $resultingExitCode === 0 ? $exitCode : $resultingExitCode;
        }
        return $resultingExitCode;
    }
}
