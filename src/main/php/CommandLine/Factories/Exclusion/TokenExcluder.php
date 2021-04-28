<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories\Exclusion;

use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use function Safe\substr;

class TokenExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * TokenExcluder constructor.
     *
     * @param Environment             $environment
     * @param ProcessRunner           $processRunner
     * @param EnhancedFileInfoFactory $enhancedFileInfoFactory
     */
    public function __construct(
        Environment $environment,
        ProcessRunner $processRunner,
        EnhancedFileInfoFactory $enhancedFileInfoFactory
    ) {
        $this->environment = $environment;
        $this->processRunner = $processRunner;
        $this->enhancedFileInfoFactory = $enhancedFileInfoFactory;
    }

    /**
     * This method searches for paths which contain a file by the name of $config['token']. It will not search in
     * $alreadyExcludedPaths to speed things up.
     *
     * @param array<string> $alreadyExcludedPaths
     * @param array<mixed>  $config
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        if (!isset($config['token'])) {
            return [];
        }
        $token = $config['token'];

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -name ' . $token . $excludeParameters
        );

        if (empty($finderResult)) {
            return [];
        }

        $rawExcludePathsByToken = explode(PHP_EOL, trim($finderResult));
        $absoluteDirectories = array_map('dirname', $rawExcludePathsByToken);
        $relativeDirectories = array_map(
            static function ($value) use ($rootDirectory) {
                $rootDirectoryLength = strlen($rootDirectory);
                if (strlen($value) === $rootDirectoryLength) {
                    return '.';
                }
                return substr($value, strlen($rootDirectory) + 1);
            },
            $absoluteDirectories
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($relativeDirectories);

        return $result;
    }
}
