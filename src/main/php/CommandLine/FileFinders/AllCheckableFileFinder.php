<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class AllCheckableFileFinder implements FileFinderInterface
{
    /** @var ProcessRunner */
    private $processRunner;
    /** @var FileFilter */
    private $fileFilter;

    /**
     * AllCheckableFileFinder constructor.
     *
     * @param ProcessRunner $processRunner
     * @param FileFilter    $fileFilter
     */
    public function __construct(
        ProcessRunner $processRunner,
        FileFilter $fileFilter
    ) {
        $this->processRunner = $processRunner;
        $this->fileFilter    = $fileFilter;
    }

    /**
     * This function finds all files to check.
     *
     * @param string $stopword
     * @param string $filter
     * @param string $__unused
     *
     * @return string[]
     */
    public function findFiles($filter = '', $stopword = '', $__unused = '')
    {
        $filesFromGit = explode("\n", trim($this->processRunner->runAsProcess('git ls-files')));

        $result = $this->fileFilter->filterByBlacklistFilterStringAndStopword($filesFromGit, $filter, $stopword);

        return $result;
    }
}
