<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use ComposerLocator;
use SplFileInfo;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

class SmartFileInfoFactory
{
    /** @var array<int,SmartFileInfo> */
    private array $filePool;
    private FinderSanitizer $finderSanitizer;

    /**
     * SmartFileInfoFactory constructor.
     *
     * @param FinderSanitizer $finderSanitizer
     */
    public function __construct(
        FinderSanitizer $finderSanitizer
    ) {
        $this->finderSanitizer = $finderSanitizer;
    }

    /**
     * Creates SmartFileInfo for each path given in $filePath. If a path references a file
     * which does not exists on the filesystem the SmartFileInfo will not be created and
     * you will *NOT* be informed about this.
     *
     * @param array<string> $filePaths
     *
     * @return array<SmartFileInfo>
     */
    public function buildFromArrayOfPaths(array $filePaths): array
    {
        $smartFileInfoInstancesOrNull = array_map(
            function ($value) {
                try {
                    return $this->buildFromPath($value);
                } catch (FileNotFoundException $fileNotFoundException) {
                    return null;
                }
            },
            $filePaths
        );
        $smartFileInfos = array_filter($smartFileInfoInstancesOrNull);
        return array_unique($smartFileInfos);
    }

    /**
     * Creates a SmartFileInfo from the fielPath. Expect exceptions if you try this with files which
     * do not exist on the Filesystem.
     *
     * SmartFileFactory does its best to  return the same instance of SmartFileInfo for a file.
     * It uses Inode to distinguish between Files.
     *
     * @param string $filePath
     *
     * @return SmartFileInfo
     *
     * @throws FileNotFoundException
     */
    public function buildFromPath(string $filePath): SmartFileInfo
    {
        if ($filePath[0] !== '/') {
            $filePath = ComposerLocator::getRootPath() . DIRECTORY_SEPARATOR . $filePath;
            $this->checkPath($filePath);
        }

        $fileinode = fileinode($filePath);

        if (isset($this->filePool[$fileinode])) {
            return $this->filePool[$fileinode];
        }

        $this->filePool[$fileinode] = new SmartFileInfo($filePath);

        return $this->filePool[$fileinode];
    }

    /**
     * Checks if file exists on filesystem and throws exception if not.
     *
     * @param string $filePath
     *
     * @throws FileNotFoundException
     */
    private function checkPath(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(
                $filePath . ' is not a valid Path. Can\'t create SmartFileInfo from that.',
                1610034580
            );
        }
    }

    /**
     * If you ever get a SplFileInfo (or a child class) and want them to be a SmartFileInfo
     * and be managed by SmartFileInfoFactory giv it to this method.
     *
     * You will get Exceptions if the referenced file does not exist on the filesystem.
     *
     * @param SplFileInfo $splFileInfo
     *
     * @return SmartFileInfo
     *
     * @throws FileNotFoundException
     */
    public function sanitize(SplFileInfo $splFileInfo): SmartFileInfo
    {
        $filePath = $splFileInfo->getPathname();
        $this->checkPath($filePath);

        $fileInode = fileinode($filePath);

        if (isset($this->filePool[$fileInode])) {
            return $this->filePool[$fileInode];
        }

        $smartFileInfos = $this->finderSanitizer->sanitize([$splFileInfo]);
        $this->filePool[$fileInode] = reset($smartFileInfos);

        return $this->filePool[$fileInode];
    }

    /**
     * Does the same as SmartFileInfoFactory::sanitize() but for array of filePaths.
     *
     * @param array<SplFileInfo> $splFileInfos
     *
     * @return array<SmartFileInfo>
     */
    public function sanitizeArray(array $splFileInfos): array
    {
        return array_unique(array_map([$this, 'sanitize'], $splFileInfos));
    }
}
