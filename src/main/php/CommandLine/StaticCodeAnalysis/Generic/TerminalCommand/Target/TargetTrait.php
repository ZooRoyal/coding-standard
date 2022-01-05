<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;

trait TargetTrait
{
    /** @var array<EnhancedFileInfo>|null */
    protected ?array $targetedFiles = null;

    /**
     * Specifies a set of files which have to be check.
     *
     * @param array<EnhancedFileInfo> $targetedFiles
     */
    final public function addTargets(array $targetedFiles): void
    {
        if ($this->targetedFiles === null) {
            $this->targetedFiles = [];
        }
        $this->targetedFiles = [...$this->targetedFiles, ...$targetedFiles];
    }

    /**
     * This method checks if the terminal command is specifically ordered to check nothing.
     *
     * @throws NoUsefulCommandFoundException
     */
    private function validateTargets(): void
    {
        if ($this->targetedFiles === []) {
            throw new NoUsefulCommandFoundException(
                'It makes no sense to sniff no files.',
                1620831304
            );
        }
    }
}
