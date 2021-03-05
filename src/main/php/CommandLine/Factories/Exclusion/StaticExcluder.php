<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class StaticExcluder implements ExcluderInterface
{
    private Environment $environment;
    /** @var array<string> */
    private array $pathsToExclude
        = [
            '.eslintrc.js',
            '.git',
            '.idea',
            '.vagrant',
            'node_modules',
            'vendor',
            'bower_components',
            '.pnpm',
            '.pnpm-store',
        ];
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * StaticExcluder constructor.
     *
     * @param Environment             $environment
     * @param EnhancedFileInfoFactory $enhancedFileInfoFactory
     */
    public function __construct(
        Environment $environment,
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->environment = $environment;
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }

    /**
     * This method searches for default directories and returns them if it finds them.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $filteredDirectories = array_filter(
            $this->pathsToExclude,
            static function ($value) use ($rootDirectory) {
                return is_dir($rootDirectory . DIRECTORY_SEPARATOR . $value);
            }
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths(array_values($filteredDirectories));

        return $result;
    }
}
