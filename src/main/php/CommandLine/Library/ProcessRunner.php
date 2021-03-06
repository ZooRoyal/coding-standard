<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use PackageVersions\Versions;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string $command
     * @param string ...$arguments Multiple strings interpreted as Arguments
     */
    public function runAsProcess(string $command, string ...$arguments): string
    {
        $process = $this->createProcess($command, ...$arguments);
        $process->mustRun();

        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        $result = empty($errorOutput)
            ? $output
            : $output . PHP_EOL . $errorOutput;

        return trim($result);
    }

    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string $command
     */
    public function runAsProcessReturningProcessObject(string $command): Process
    {
        $process = $this->createProcess($command);
        $process->run();

        return $process;
    }

    public function createProcess(string $command, string ...$arguments): Process
    {
        $version = Versions::getVersion('symfony/process');
        if ((int) $version[1] <= 3) {
            /** @phpstan-ignore-next-line */
            $process = new Process(trim($command . ' ' . implode(' ', $arguments)));
        } else {
            $process = new Process([...explode(' ', $command), ...$arguments]);
        }
        $process->setTimeout(null);
        $process->setIdleTimeout(120);

        return $process;
    }
}
