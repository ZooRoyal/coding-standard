<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\Factories\Excluders;

use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class ExclusionListSanitizer
{
    /**
     * This method deletes entries from exclusionList which would have no effect.
     *
     * @param array<EnhancedFileInfo> $rawExcludePaths
     *
     * @example
     *         Input: ['./a', './a/b', './a/b/c']
     *         Output: ['./a']
     *         Explanation: As the second and the third directories are children of the first it would make
     *         no sense to exclude them "again". As the parent is excluded they are automatically
     *         excluded too.
     *
     * @return array<EnhancedFileInfo>
     */
    public function sanitizeExclusionList(array $rawExcludePaths): array
    {
        $filteredArray = $rawExcludePaths;
        $count = count($filteredArray);
        for ($i = 0; $count > $i; $i++) {
            if (!isset($filteredArray[$i])) {
                continue;
            }
            $item = $filteredArray[$i];
            $filteredArray = array_filter(
                $filteredArray,
                static function ($value, $key) use ($item, $i): bool {
                    if ($key === $i) {
                        return true;
                    }
                    return !$value->startsWith((string) $item);
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        $filteredArray = array_values($filteredArray);

        return $filteredArray;
    }
}
