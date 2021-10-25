<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class TerminalCommandRunner
{
    private ProcessRunner $processRunner;
    private OutputInterface $output;

    public function __construct(ProcessRunner $processRunner, OutputInterface $output)
    {
        $this->processRunner = $processRunner;
        $this->output = $output;
    }

    public function run(TerminalCommand $terminalCommand): ?int
    {
        $output = $this->output;
        $process = $this->processRunner->createProcess(...$terminalCommand->toArray());
        $process->start(
            static function () use ($process, $output): void {
                $errorIncrement = $process->getIncrementalErrorOutput();
                $outputIncrement = $process->getIncrementalOutput();
                if (!empty($errorIncrement)) {
                    $output->write($errorIncrement, false, Output::OUTPUT_RAW);
                }
                if (!empty($outputIncrement)) {
                    $output->write($outputIncrement, false, Output::OUTPUT_RAW);
                }
            }
        );
        $process->wait();

        return $process->getExitCode();
    }
}
