<?php
namespace Zooroyal\CodingStandard\CommandLine\Library;

use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;

class FileFilter
{
    /** @var BlacklistFactory */
    private $blacklistFactory;
    /** @var string[] */
    private $blacklist;

    /**
     * FileFilter constructor.
     *
     * @param BlacklistFactory $blacklistFactory
     */
    public function __construct(BlacklistFactory $blacklistFactory)
    {
        $this->blacklistFactory = $blacklistFactory;
    }

    /**
     * Filters filepaths by filter and global Blacklist.
     *
     * @param string[] $filePaths
     * @param string   $filter
     * @param string   $stopword
     *
     * @return array
     */
    public function filterByBlacklistFilterStringAndStopword($filePaths, $filter = '', $stopword = '')
    {
        $this->blacklist = $this->blacklistFactory->build($stopword);
        $blacklist       = $this->blacklist;

        $result = array_filter(
            $filePaths,
            function ($value) use ($blacklist, $filter) {
                //Filter by Blacklist
                foreach ($blacklist as $item) {
                    if (0 === strpos($value, $item)) {
                        return false;
                    }
                }

                //Filter by filter.
                $length = strlen($filter);

                return $length === 0 || (substr($value, -$length) === $filter);
            }
        );

        return $result;
    }
}
