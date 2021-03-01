<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Factories\Excluders;

use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\ExcluderInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class StaticExcluder implements ExcluderInterface
{
    /** @var array<string> */
    private const PATHS_TO_EXCLUDE = [
        '.eslintrc.js',
        '.git',
        '.idea',
        '.vagrant',
        'node_modules',
        'vendor',
        'var',
        'bower_components',
        '.pnpm',
        '.pnpm-store',
    ];
    /**
     * StaticExcluder constructor.
     */
    public function __construct(
        private Environment $environment,
        private EnhancedFileInfoFactory $enhancedFileInfoFactory,
    ) {
    }

    /**
     * This method searches for default directories and returns them if it finds them.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $filteredDirectories = array_filter(
            self::PATHS_TO_EXCLUDE,
            static function ($value) use ($rootDirectory): bool {
                return is_dir($rootDirectory . DIRECTORY_SEPARATOR . $value);
            }
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths(array_values($filteredDirectories));

        return $result;
    }
}
