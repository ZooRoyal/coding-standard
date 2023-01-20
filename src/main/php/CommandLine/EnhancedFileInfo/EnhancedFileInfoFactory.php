<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo;

use InvalidArgumentException;
use Webmozart\PathUtil\Path;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

use function Safe\fileinode;

class EnhancedFileInfoFactory
{
    /** @var array<int,EnhancedFileInfo> */
    private array $filePool = [];
    private string $rootDirectory;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->rootDirectory = $processRunner->runAsProcess('git', 'rev-parse', '--show-toplevel');
    }

    /**
     * Creates a EnhancedFileInfo from the filePath. Expect exceptions if you try this with files which
     * do not exist on the Filesystem.
     *
     * SmartFileFactory does its best to  return the same instance of EnhancedFileInfo for a file.
     * It uses Inode to distinguish between Files.
     */
    public function buildFromPath(string $pathName): EnhancedFileInfo
    {
        $pathName = Path::canonicalize($pathName);
        if (!Path::isAbsolute($pathName)) {
            $pathName = $this->rootDirectory . DIRECTORY_SEPARATOR . $pathName;
        }
        $this->checkPath($pathName);

        $fileINode = fileinode($pathName);

        if (isset($this->filePool[$fileINode])) {
            return $this->filePool[$fileINode];
        }

        $this->filePool[$fileINode] = new EnhancedFileInfo($pathName, $this->rootDirectory);

        return $this->filePool[$fileINode];
    }

    /**
     * Creates EnhancedFileInfo for each path given in $filePath. If a path references a file
     * which does not exist on the filesystem the EnhancedFileInfo will not be created, and
     * you will *NOT* be informed about this.
     *
     * @param array<string> $pathNames
     *
     * @return array<EnhancedFileInfo>
     */
    public function buildFromArrayOfPaths(array $pathNames): array
    {
        $enhancedFileInfoInstancesOrNull = array_map(
            function ($value): ?EnhancedFileInfo {
                try {
                    return $this->buildFromPath($value);
                } catch (InvalidArgumentException) {
                    return null;
                }
            },
            $pathNames,
        );
        $enhancedFileInfos = array_filter($enhancedFileInfoInstancesOrNull);
        return array_unique($enhancedFileInfos);
    }

    /**
     * Checks if file exists on filesystem and throws exception if not.
     *
     * @throws InvalidArgumentException
     */
    private function checkPath(string $pathName): void
    {
        if (!file_exists($pathName)) {
            throw new InvalidArgumentException($pathName . ' could not be found.', 1610034580);
        }
    }
}
