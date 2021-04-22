<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TargetableTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\ExcludingTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\TargetableTrait;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetableTerminalCommand,
    FileExtensionTerminalCommand,
    ExcludingTerminalCommand
{
    use TargetableTrait, ExcludingTrait, FileExtensionTrait;

    private const TEMPLATE = 'php %1$s %2$s text %3$s%5$s%4$s';
    private Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        $vendorPath = $this->environment->getVendorPath()->getRealPath();
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
                $this->excludesFiles
            );
            $excludingString .= implode(',', $excludesFilePaths);
        };
        return $excludingString;
    }

    /**
     * This method returns the string representation of the targeted files list.
     */
    private function buildTargetingString(): string
    {
        if ($this->targetedFiles !== []) {
            $targetedFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => $item->getRealPath(),
                $this->targetedFiles
            );
            $targetingString = implode(',', $targetedFilePaths);
        } else {
            $targetingString = $this->environment->getRootDirectory()->getRealPath();
        }
        return $targetingString;
    }

    private function buildExtensionString()
    {
        $extensionString = '';
        if ($this->fileExtensions !== []) {
            $extensionString = ' --suffixes ';
            $extensionString .= implode(',', $this->fileExtensions);
        }
        return $extensionString;
    }
}
