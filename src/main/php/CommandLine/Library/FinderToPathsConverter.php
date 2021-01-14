<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;

/**
 * This converter is able to extract data from a
 * Symfony\Component\Finder\Finder object and returns them
 * in a more useful data structure.
 */
class FinderToPathsConverter
{
    private SmartFileInfoFactory $smartFileInfoFactory;

    /**
     * FinderToPathsConverter constructor.
     *
     * @param SmartFileInfoFactory $smartFileInfoFactory
     */
    public function __construct(SmartFileInfoFactory $smartFileInfoFactory)
    {
        $this->smartFileInfoFactory = $smartFileInfoFactory;
    }


    /**
     * Returns directories of found files.
     *
     * @param Finder $tokenFinder
     *
     * @return array<SmartFileInfo>
     */
    public function finderToArrayOfDirectories(Finder $tokenFinder): array
    {
        $excludedPathsAsStrings = array_unique(
            array_values(
                array_map(
                    static fn($tokenFile) => $tokenFile->getPath(),
                    iterator_to_array($tokenFinder)
                )
            )
        );

        $rawExcludePathsByToken = $this->smartFileInfoFactory->buildFromArrayOfPaths($excludedPathsAsStrings);
        return $rawExcludePathsByToken;
    }

    /**
     * Converts Finder objects to Arrays of their relative paths without the filename.
     *
     * @param Finder $finder
     *
     * @return array<SmartFileInfo>
     */
    public function finderToArray(Finder $finder): array
    {
        /** @var array<SplFileInfo> $splFileInfos */
        $splFileInfos = iterator_to_array($finder);
        $smartFileInfos = $this->smartFileInfoFactory->sanitizeArray($splFileInfos);
        $smartFileInfosValues = array_values($smartFileInfos);

        return array_unique($smartFileInfosValues);
    }

}
