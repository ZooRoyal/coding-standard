<?php
namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string   $command
     * @param string[] $arguments Multiple strings interpreted as Arguments
     *
     * @return string
     */
    public function runAsProcess($command, ...$arguments)
    {
        $commandParts = array_merge([$command], $arguments);
        $process      = new Process($commandParts);
        $process->mustRun()->wait();

        $output = $process->getOutput();
        $result = empty($process->getErrorOutput())
            ? $output
            : $output . "\n" . $process->getErrorOutput();

        return trim($result);
    }

    /**
     * Runs a shell command as single Process and returns the reseult.
     *
     * @param string $command
     *
     * @return Process
     */
    public function runAsProcessReturningProcessObject($command)
    {
        $process = new Process($command);
        $process->run();
        $process->setTimeout(null);
        $process->setIdleTimeout(60);
        $process->wait();

        return $process;
    }
}
