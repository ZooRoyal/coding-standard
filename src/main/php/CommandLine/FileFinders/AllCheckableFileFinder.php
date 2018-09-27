<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Factories\GitChangeSetFactory;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class AllCheckableFileFinder implements FileFinderInterface
{
    /** @var ProcessRunner */
    private $processRunner;
    /** @var FileFilter */
    private $fileFilter;
    /** @var GitChangeSetFactory */
    private $gitChangeSetFactory;

    /**
     * AllCheckableFileFinder constructor.
     *
     * @param ProcessRunner       $processRunner
     * @param FileFilter          $fileFilter
     * @param GitChangeSetFactory $gitChangeSetFactory
     */
    public function __construct(
        ProcessRunner $processRunner,
        FileFilter $fileFilter,
        GitChangeSetFactory $gitChangeSetFactory
    ) {
        $this->processRunner       = $processRunner;
        $this->fileFilter          = $fileFilter;
        $this->gitChangeSetFactory = $gitChangeSetFactory;
    }

    /**
     * This function finds all files to check.
     *
     * @param string $filter
     * @param string $stopword
     * @param string $__unused
     *
     * @return GitChangeSet
     */
    public function findFiles($filter = '', $stopword = '', $__unused = '')
    {
        $filesFromGit = explode("\n", trim($this->processRunner->runAsProcess('git', 'ls-files')));
        $gitChangeSet = $this->gitChangeSetFactory->build($filesFromGit, '');

        $this->fileFilter->filterByBlacklistFilterStringAndStopword($gitChangeSet, $filter, $stopword);

        return $gitChangeSet;
    }
}
