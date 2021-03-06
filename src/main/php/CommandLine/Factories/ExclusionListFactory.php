<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Annotation\Inject;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\ExcluderInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\ExclusionListSanitizer;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class ExclusionListFactory
{
    private array $exclusionListCache = [];
    /** @var array<ExcluderInterface> */
    private array $excluders;
    private ExclusionListSanitizer $exclusionListSanitizer;

    /**
     * BlacklistFactory constructor.
     *
     * @param array<ExcluderInterface> $excluders
     * @param ExclusionListSanitizer   $exclusionListSanitizer
     *
     * @Inject({"excluders" = "excluders"})
     */
    public function __construct(array $excluders, ExclusionListSanitizer $exclusionListSanitizer)
    {
        $this->excluders = $excluders;
        $this->exclusionListSanitizer = $exclusionListSanitizer;
    }

    /**
     * This function computes a blacklist of directories which should not be checked.
     *
     * @param string $token
     * @param bool   $deDuped
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
            $excludedPaths = array_merge($excludedPaths, $newlyFoundExclusionPaths);
        }

        $filteredArray = $deDuped === true
            ? $this->exclusionListSanitizer->sanitizeExclusionList($excludedPaths)
            : $excludedPaths;

        $this->exclusionListCache[$token . $deDuped] = $filteredArray;

        return $filteredArray;
    }
}
