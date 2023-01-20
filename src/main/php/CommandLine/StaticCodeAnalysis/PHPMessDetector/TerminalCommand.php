<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTrait;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetTerminalCommand,
    FileExtensionTerminalCommand,
    ExclusionTerminalCommand
{
    use TargetTrait;
    use ExclusionTrait;
    use FileExtensionTrait;

    private const TEMPLATE = 'php %1$s %2$s text %3$s%5$s%4$s';

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
        $phpMessDetectorConfig = $this->environment->getPackageDirectory()->getRealPath()
            . '/config/phpmd/phpmd.xml';

        $terminalApplication = $vendorPath . '/bin/phpmd';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $terminalApplication,
            $this->buildTargetingString(),
            $phpMessDetectorConfig,
            $this->buildExcludingString(),
            $this->buildExtensionString(),
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
            $excludingString = ' --exclude ';
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => $item->getRealPath(),
                $this->excludesFiles,
            );
            $excludingString .= implode(',', $excludesFilePaths);
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
                static fn(EnhancedFileInfo $item) => $item->getRealPath(),
                $this->targetedFiles,
            );
            $targetingString = implode(',', $targetedFilePaths);
        } else {
            $targetingString = $this->environment->getRootDirectory()->getRealPath();
        }
        return $targetingString;
    }

    private function buildExtensionString(): string
    {
        $extensionString = '';
        if ($this->fileExtensions !== []) {
            $extensionString = ' --suffixes ';
            $extensionString .= implode(',', $this->fileExtensions);
        }
        return $extensionString;
    }
}
