<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\MultiprocessTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TargetableTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\ExcludingTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\MultiprocessTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\TargetableTrait;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetableTerminalCommand,
    ExcludingTerminalCommand,
    FileExtensionTerminalCommand,
    MultiprocessTerminalCommand
{
    use TargetableTrait, ExcludingTrait, FileExtensionTrait, MultiprocessTrait;

    private const TEMPLATE = 'php %1$s -j %5$d%2$s%3$s%4$s';
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
        $this->validateTargets();

        $vendorPath = $this->environment->getVendorPath()->getRealPath();

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
                $this->excludesFiles
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
                $this->targetedFiles
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
            $extensionList = ' -e '.$extensionList;
        }

        return $extensionList;
    }
}
