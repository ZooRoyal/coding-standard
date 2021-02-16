<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class StaticExcluder implements ExcluderInterface
{
    private Environment $environment;
    /** @var array<string>  */
    private array $pathsToExclude = [
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

    /**
     * StaticExcluder constructor.
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * This method searches for default directories and returns them if it finds them.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<string>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $rootDirectory = $this->environment->getRootDirectory();

        $filteredDirectories = array_filter(
            $this->pathsToExclude,
            static function ($value) use ($rootDirectory) {
                return is_dir($rootDirectory . DIRECTORY_SEPARATOR . $value);
            }
        );

        return array_values($filteredDirectories);
    }
}
