<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use function Safe\sprintf;

class GitIgnoresExcluder implements ExcluderInterface
{
    private const FIND_COMMAND = 'find %s -type d%s';
    private const GIT_CHECK_COMMAND = 'git check-ignore --stdin';
    private const EXCLUDE_PARAMS = ' -not -path %s/*';
    private const PARAMS_SEPARATOR = '/* -not -path ';
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
        $rawFoundFolders = $this->findFoldersNotYetExcluded($alreadyExcludedPaths);
        $rawIgnoredFoldersOutput = $this->filterForDirectoriesKnownToGit($rawFoundFolders);

        if (empty($rawIgnoredFoldersOutput)) {
            return [];
        }
        $ignoredFolders = explode("\n", $rawIgnoredFoldersOutput);
        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($ignoredFolders);

        return $result;
    }

    /**
     * Uses find command to get all directories.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     */
    private function findFoldersNotYetExcluded(array $alreadyExcludedPaths): string
    {
        $excludeParameters = '';
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = sprintf(
                self::EXCLUDE_PARAMS,
                implode(
                    self::PARAMS_SEPARATOR,
                    $alreadyExcludedPaths
                )
            );
        }

        $findCommand = sprintf(self::FIND_COMMAND, $rootDirectory, $excludeParameters);

        $rawFoundFolders = $this->processRunner->runAsProcess($findCommand);
        return $rawFoundFolders;
    }

    /**
     * Get all directories which are known to git and deemed to be ignored.
     *
     * @throws ProcessFailedException because it's only a problem if exitCode is not 0 or 1. We have to check for
     *                                that and therefore intercept the exception.
     */
    private function filterForDirectoriesKnownToGit(string $rawFoundFolders): string
    {
        $checkProcess = $this->processRunner->createProcess(self::GIT_CHECK_COMMAND);

        $rawIgnoredFoldersOutput = '';
        try {
            $checkProcess->setInput($rawFoundFolders);
            $rawIgnoredFoldersOutput = trim($checkProcess->mustRun()->getOutput());
        } catch (ProcessFailedException $exception) {
            $exitCode = $exception->getProcess()->getExitCode();
            if ($exitCode !== 1) {
                throw $exception;
            }
        }
        return $rawIgnoredFoldersOutput;
    }
}
