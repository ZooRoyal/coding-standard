<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\Factories\Excluders;

use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\ExcluderInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\substr;

class GitPathsExcluder implements ExcluderInterface
{
    private Environment $environment;
    private ProcessRunner $processRunner;
    private EnhancedFileInfoFactory $enhancedFileInfoFactory;

    /**
     * GitPathsExcluder constructor.
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
     * The methods searches for Git submodules and returns their paths.
     *
     * @param array<EnhancedFileInfo> $alreadyExcludedPaths
     * @param array<mixed>  $config
     *
     * @return array<EnhancedFileInfo>
     */
    public function getPathsToExclude(array $alreadyExcludedPaths, array $config = []): array
    {
        $excludeParameters = '';
        if (!empty($alreadyExcludedPaths)) {
            $excludeParameters = ' -not -path "./' . implode('" -not -path "./', $alreadyExcludedPaths) . '"';
        }

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();
        $finderResult = $this->processRunner->runAsProcess(
            'find ' . $rootDirectory . ' -mindepth 2 -name .git' . $excludeParameters
        );

        if (empty($finderResult)) {
            return [];
        }

        $rawExcludePathsByFileByGit = explode(PHP_EOL, trim($finderResult));

        $relativeDirectories = array_map(
            static fn($value): string => substr(dirname($value), strlen($rootDirectory) + 1),
            $rawExcludePathsByFileByGit
        );

        $result = $this->enhancedFileInfoFactory->buildFromArrayOfPaths($relativeDirectories);

        return $result;
    }
}
