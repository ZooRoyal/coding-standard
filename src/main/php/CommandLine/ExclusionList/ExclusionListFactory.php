<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList;

use DI\Attribute\Inject;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\ExcluderInterface;

class ExclusionListFactory
{
    /** @var array<string,array<EnhancedFileInfo>> */
    private array $exclusionListCache = [];
    /**
     * BlacklistFactory constructor.
     *
     * @param array<ExcluderInterface> $excluders
     */
    public function __construct(
        #[Inject('excluders')] private array $excluders,
        private ExclusionListSanitizer $exclusionListSanitizer,
    ) {
    }

    /**
     * This function computes a exclusionlist of directories which should not be checked.
     *
     * @return array<EnhancedFileInfo>
     */
    public function build(string $token = '', bool $deDuped = true): array
    {
        if (array_key_exists($token . $deDuped, $this->exclusionListCache)) {
            return $this->exclusionListCache[$token . $deDuped];
        }

        $config = [];
        if ($token !== '') {
            $config = ['token' => $token];
        }

        $excludedPaths = [];

        foreach ($this->excluders as $excluder) {
            $newlyFoundExclusionPaths = $excluder->getPathsToExclude($excludedPaths, $config);
            $excludedPaths = [...$excludedPaths, ...$newlyFoundExclusionPaths];
        }

        $filteredArray = $deDuped === true
            ? $this->exclusionListSanitizer->sanitizeExclusionList($excludedPaths)
            : $excludedPaths;

        $this->exclusionListCache[$token . $deDuped] = $filteredArray;

        return $filteredArray;
    }
}
