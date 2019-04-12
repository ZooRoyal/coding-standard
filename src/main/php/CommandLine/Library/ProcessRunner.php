<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string               $command
     * @param string|string[]|null ...$arguments Multiple strings interpreted as Arguments
     *
     * @return string
     */
    public function runAsProcess(string $command, ...$arguments) : string
    {
        $commandParts = implode(' ', array_merge([$command], $arguments));
        $process = new Process($commandParts);
        $process->mustRun()->wait();

        $output = $process->getOutput();
        $result = empty($process->getErrorOutput())
            ? $output
            : $output . PHP_EOL . $process->getErrorOutput();

        return trim($result);
    }

    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string $command
     *
     * @return Process
     */
    public function runAsProcessReturningProcessObject($command) : Process
    {
        $process = new Process($command);
        $process->run();
        $process->setTimeout(null);
        $process->setIdleTimeout(60);
        $process->wait();

        return $process;
    }
}
