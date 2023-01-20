<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTrait;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetTerminalCommand,
    ExclusionTerminalCommand,
    FileExtensionTerminalCommand,
    MultiprocessTerminalCommand
{
    use TargetTrait;
    use ExclusionTrait;
    use FileExtensionTrait;
    use MultiprocessTrait;

    private const TEMPLATE = 'php %1$s -j %5$d%2$s%3$s%4$s';

    public function __construct(private readonly Environment $environment)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        $this->validateTargets();

        $vendorPath = $this->environment->getVendorDirectory()->getRealPath();

        $terminalApplication = $vendorPath . '/bin/parallel-lint';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $terminalApplication,
            $this->buildExcludingString(),
            $this->buildExtensionString(),
            $this->buildTargetingString(),
            $this->maxConcurrentProcesses,
        );

        $this->command = $sprintfCommand;
        $this->commandParts = explode(' ', $sprintfCommand);
    }

    /**
     * This method returns the string representation of the excluded files list.
     */
    private function buildExcludingString(): string
    {
        $excludingString = '';
        if ($this->excludesFiles !== []) {
            $excludingString = ' ';
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => '--exclude ' . $item->getRelativePathname() . '/',
                $this->excludesFiles,
            );
            $excludingString .= implode(' ', $excludesFilePaths);
        }
        return $excludingString;
    }

    /**
     * This method returns the string representation of the targeted files list.
     */
    private function buildTargetingString(): string
    {
        if ($this->targetedFiles !== null) {
            $targetedFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => $item->getRelativePathname(),
                $this->targetedFiles,
            );
            $targetingString = ' ' . implode(' ', $targetedFilePaths);
        } else {
            $targetingString = ' .';
        }
        return $targetingString;
    }

    private function buildExtensionString(): string
    {
        $extensionList = implode(',', $this->fileExtensions);

        if ($extensionList !== '') {
            $extensionList = ' -e ' . $extensionList;
        }

        return $extensionList;
    }
}
