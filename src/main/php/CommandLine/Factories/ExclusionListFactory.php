<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Annotation\Inject;
use Zooroyal\CodingStandard\CommandLine\Factories\Excluders\ExclusionListSanitizer;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class ExclusionListFactory
{
    /** @var mixed[] */
    private array $exclusionListCache = [];
    /** @var array<ExcluderInterface> */
    private array $excluders;
    private ExclusionListSanitizer $exclusionListSanitizer;

    /**
     * BlacklistFactory constructor.
     *
     * @param array<ExcluderInterface> $excluders
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
