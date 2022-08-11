<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;

class GitIgnoresExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * GitIgnoresExcluder constructor.
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
     * This Method ask Git which directories should be ignored and returns them if they are found.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>            $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $alreadyExcludedPathsAsRelativPaths = array_map(
                static fn($value): string => $value->getRelativePathname(),
                $alreadyExcludedPaths
            );
            $excludeParameters = ' -not -path "./'
                . implode('/*" -not -path "./', $alreadyExcludedPathsAsRelativPaths)
                . '/*"';
        }

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();
        $command = 'find ' . $rootDirectory . ' -type d' . $excludeParameters . ' | git check-ignore --stdin';

        $rawIgnoredFoldersOutput = $this->processRunner->runAsProcess($command);

        $ignoredFolders = explode("\n", trim($rawIgnoredFoldersOutput));

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($ignoredFolders);

        return $result;
    }
}
