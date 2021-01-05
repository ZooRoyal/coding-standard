<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * This converter is able to extract data from a
 * Symfony\Component\Finder\Finder object and returns them
 * in a more useful data structure.
 */
class FinderToPathsConverter
{
    /**
     * Converts Finder objects to Arrays of their relative paths without the filename.
     *
     * @param Finder $finder
     *
     * @return string[]
     */
    public function finderToArrayOfPaths(Finder $finder): array
    {
        $directories = array_map(
            static function (SplFileInfo $value) {
                if ($value->isDir()) {
                    return $value->getRelativePathname() . '/';
                }
                if ($value->getRelativePath() === '') {
                    return './';
                }
                return $value->getRelativePath() . '/';
            },
            iterator_to_array($finder)
        );

        return array_unique(array_values($directories));
    }
}
