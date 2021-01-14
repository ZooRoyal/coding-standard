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
     * @param string|string[]|null ...$arguments Multiple strings interpreted as Arguments
     *
     * @return string
     */
    public function runAsProcess(string $command, ...$arguments): string
    {
        $version = Versions::getVersion('symfony/process');
        if ((int) $version[1] <= 3) {
            /** @phpstan-ignore-next-line */
            $process = new Process($command . ' ' . implode(' ', $arguments));
        } else {
            $process = new Process(array_merge(explode(' ', $command), $arguments));
        }
        $process->mustRun();
        $process->wait();

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
    public function runAsProcessReturningProcessObject(string $command): Process
    {
        $version = Versions::getVersion('symfony/process');
        if ((int) $version[1] <= 3) {
            /** @phpstan-ignore-next-line */
            $process = new Process($command);
        } else {
            $process = new Process(explode(' ', $command));
        }
        $process->setTimeout(null);
        $process->setIdleTimeout(60);
        $process->run();
        $process->wait();

        return $process;
    }
}
