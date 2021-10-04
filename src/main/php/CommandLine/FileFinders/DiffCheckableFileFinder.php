<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class DiffCheckableFileFinder implements FileFinderInterface
{
    private ProcessRunner $processRunner;
    private GitChangeSetFilter $fileFilter;
    private GitChangeSetFactory $gitChangeSetFactory;

    /**
     * CheckableFileFinder constructor.
     */
    public function __construct(
        ProcessRunner $processRunner,
        GitChangeSetFilter $fileFilter,
        GitChangeSetFactory $gitChangeSetFactory
    ) {
        $this->processRunner = $processRunner;
        $this->fileFilter = $fileFilter;
        $this->gitChangeSetFactory = $gitChangeSetFactory;
    }

    /**
     * This function searches for files to check in a certain diff only.
     *
     * @param string[]    $allowedFileEndings
     *
     * @throws InvalidArgumentException
     */
    public function findFiles(
        array $allowedFileEndings = [],
        string $blacklistToken = '',
        string $whitelistToken = '',
        ?string $targetBranch = null
    ): GitChangeSet {
        if ($targetBranch === null || $targetBranch === '') {
            throw new InvalidArgumentException(
                'Finding a diff makes no sense without a target branch.',
                1553857649
            );
        }

        $rawDiff = $this->findFilesInDiffToTarget($targetBranch);
        $this->fileFilter->filter($rawDiff, $allowedFileEndings, $blacklistToken, $whitelistToken);

        return $rawDiff;
    }

    /**
     * This method finds all files in diff to target branch.
     */
    private function findFilesInDiffToTarget(string $targetBranch): GitChangeSet
    {
        $mergeBase = $this->processRunner->runAsProcess('git', 'merge-base', 'HEAD', $targetBranch);

        $rawDiffUnfilteredString = $this->processRunner->runAsProcess(
            'git',
            'diff',
            '--name-only',
            '--diff-filter=d',
            $mergeBase
        );

        $rawDiffUnfiltered = explode("\n", trim($rawDiffUnfilteredString));

        $result = $this->gitChangeSetFactory->build($rawDiffUnfiltered, $mergeBase);

        return $result;
    }
}
