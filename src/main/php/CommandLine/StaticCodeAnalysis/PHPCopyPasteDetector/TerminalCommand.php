<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTrait;

class TerminalCommand extends AbstractTerminalCommand implements
    ExclusionTerminalCommand,
    FileExtensionTerminalCommand,
    TargetTerminalCommand
{
    use ExclusionTrait;
    use FileExtensionTrait;
    use TargetTrait;

    private const TEMPLATE = 'php %1$s --fuzzy %3$s%2$s%4$s';
    private const STATIC_EXCLUDES
        = [
            'custom/plugins/ZRBannerSlider/ZRBannerSlider.php',
            'custom/plugins/ZRPreventShipping/ZRPreventShipping.php',
        ];

    public function __construct(private readonly Environment $environment, private readonly ProcessRunner $processRunner)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        $vendorPath = $this->environment->getVendorDirectory()->getRealPath();

        $terminalApplication = $vendorPath . '/bin/phpcpd';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $terminalApplication,
            $this->buildExcludingString(),
            $this->buildExtensionsString(),
            $this->buildTargetingString(),
        );

        $this->command = $sprintfCommand;
        $this->commandParts = explode(' ', $sprintfCommand);
    }

    /**
     * This method returns the string representation of the excluded files list.
     */
    private function buildExcludingString(): string
    {
        if (!$this->useExclusions()) {
            return '';
        }

        $excludesFilePaths = [];
        $finderResultLines = [];
        $rootPath = $this->environment->getRootDirectory()->getRealPath();

        if ($this->excludesFiles !== []) {
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item): string => $item->getRelativePathname() . '/',
                $this->excludesFiles,
            );
        }

        $command = ['find', $rootPath, '-path', '*/custom/plugins/*', '-name', 'Installer.php', '-maxdepth', '4'];

        $finderResult = $this->processRunner->runAsProcess(...$command);

        if (!empty($finderResult)) {
            $finderResultLines = explode(PHP_EOL, trim($finderResult));
        }

        $exclusions = [...$excludesFilePaths, ...self::STATIC_EXCLUDES, ...$finderResultLines];
        $excludingString = $this->collapseExcludes($exclusions);

        return $excludingString;
    }

    private function buildExtensionsString(): string
    {
        $extensionsString = '';
        if ($this->fileExtensions !== []) {
            $extensionsString = '--suffix ' . implode(' --suffix ', $this->fileExtensions);
            $extensionsString .= ' ';
        }

        return $extensionsString;
    }

    /**
     * Collapse the excludes to a string like '--exclude asdasd --exclude qweqwe'.
     *
     * @param array<string> $finderResultLines
     */
    private function collapseExcludes(array $finderResultLines): string
    {
        return implode(
            ' ',
            array_map(
                static fn(string $item): string => '--exclude ' . $item,
                $finderResultLines,
            ),
        ) . ' ';
    }

    /**
     * This method returns the string representation of the targeted files list. This is a tricky one because we need
     * to filter out targets which are excluded by the exclusion rule.
     */
    private function buildTargetingString(): string
    {
        if ($this->useExclusions()) {
            return '.';
        }

        $filteredTargetedFiles = $this->getFilteredTargetFiles();

        $targetedFilePaths = array_map(
            static fn(EnhancedFileInfo $item) => $item->getRelativePathname(),
            $filteredTargetedFiles,
        );
        $targetingString = implode(' ', $targetedFilePaths);

        return $targetingString;
    }

    private function useExclusions(): bool
    {
        return empty($this->targetedFiles);
    }

    /**
     * Filter targeted Files by being a installer, being already excluded or part of the static exclusion list.
     *
     * @return array<EnhancedFileInfo>
     */
    private function getFilteredTargetFiles(): array
    {
        $filteredTargetedFiles = [];

        foreach ($this->targetedFiles as $targetedFile) {
            /** @var EnhancedFileInfo $targetedFile */

            if (
                $this->isInstaller($targetedFile)
                || $this->isDynamicallyExcluded($targetedFile)
                || $this->isStaticallyExcluded($targetedFile)
            ) {
                continue;
            }

            $filteredTargetedFiles[] = $targetedFile;
        }
        return $filteredTargetedFiles;
    }

    private function isInstaller(EnhancedFileInfo $targetedFile): bool
    {
        $isInstaller = false;
        if (
            $targetedFile->getFilename() === 'Installer.php'
            && str_contains($targetedFile->getPath(), '/custom/plugins/')
        ) {
            $isInstaller = true;
        }
        return $isInstaller;
    }

    private function isDynamicallyExcluded(EnhancedFileInfo $targetedFile): bool
    {
        $isDynamicallyExcluded = false;
        foreach ($this->excludesFiles as $excludesFile) {
            if ($targetedFile->startsWith($excludesFile->getPathname())) {
                $isDynamicallyExcluded = true;
            }
        }
        return $isDynamicallyExcluded;
    }

    private function isStaticallyExcluded(EnhancedFileInfo $targetedFile): bool
    {
        $isStaticallyExcluded = false;
        foreach (self::STATIC_EXCLUDES as $staticExclude) {
            if (
                $targetedFile->startsWith(
                    $this->environment->getRootDirectory()->getRealPath() . '/' . $staticExclude,
                )
            ) {
                $isStaticallyExcluded = true;
            }
        }
        return $isStaticallyExcluded;
    }
}
