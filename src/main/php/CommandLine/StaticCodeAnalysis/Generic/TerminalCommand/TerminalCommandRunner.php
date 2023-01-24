<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class TerminalCommandRunner
{
    public function __construct(private readonly ProcessRunner $processRunner, private readonly OutputInterface $output)
    {
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
                    $output->write($errorIncrement, false, OutputInterface::OUTPUT_RAW);
                }
                if (!empty($outputIncrement)) {
                    $output->write($outputIncrement, false, OutputInterface::OUTPUT_RAW);
                }
            },
        );
        $process->wait();

        return $process->getExitCode();
    }
}
