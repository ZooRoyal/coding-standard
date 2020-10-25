<?php


namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPStanAdapter;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\PHPStanInputOptions;

class PHPStanCommand extends Command
{
    /** @var PHPStanAdapter */
    private $toolAdapter;
    /** @var PHPStanInputOptions */
    private $phpstanInputOptions;

    /**
     * PHPCodeSnifferCommand constructor.
     *
     * @param PHPStanAdapter $toolAdapter
     * @param PHPStanInputOptions $phpstanInputOptions
     */
    public function __construct(
        PHPStanAdapter $toolAdapter,
        PHPStanInputOptions $phpstanInputOptions
    ) {
        $this->toolAdapter = $toolAdapter;
        $this->phpstanInputOptions = $phpstanInputOptions;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('sca:stan');
        $this->setDescription('Run PHPStan on PHP files.');
        $this->setHelp('This tool executes PHPStan on a certain set of PHP files of this project.');
        $this->setDefinition($this->getInputDefinition());
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $targetBranch = $input->getOption('auto-target') ??  $input->getOption('target');
        $processIsolationInput = $input->getOption('process-isolation');
        return $this->toolAdapter->writeViolationsToOutput($targetBranch, $processIsolationInput);
    }

     /**
      * {@inheritdoc}
      */
    protected function getInputDefinition(): InputDefinition
    {
        $inputDefinition = [];
        $inputOptions = $this->phpstanInputOptions->getInputOptions();
        foreach ($inputOptions as $inputOption) {
            $inputDefinition[] = new InputOption(
                $inputOption['name'],
                $inputOption['short'],
                $inputOption['option'],
                $inputOption['description']
            );
        }

        return new InputDefinition($inputDefinition);
    }


}
