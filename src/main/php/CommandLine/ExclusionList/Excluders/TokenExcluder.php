<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use function Safe\substr;

class TokenExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * TokenExcluder constructor.
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
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     *
     * @return array<EnhancedFileInfo>
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
