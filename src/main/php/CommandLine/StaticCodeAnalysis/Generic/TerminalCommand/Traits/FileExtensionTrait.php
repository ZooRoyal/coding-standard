<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits;

trait FileExtensionTrait
{
    /** @var array<string> */
    protected array $fileExtensions = [];

    /** @param array<string> $fileExtensions */
    public function addAllowedFileExtensions(array $fileExtensions): void
    {
        $this->fileExtensions = $fileExtensions;
    }
}
