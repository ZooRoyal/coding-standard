<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\Factories\FinderFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class ParentByFileFinder
{
    /** @var Environment */
    private $environment;
    /** @var FinderFactory */
    private $finderFactory;

    public function __construct(Environment $environment, FinderFactory $finderFactory)
    {
        $this->environment   = $environment;
        $this->finderFactory = $finderFactory;
    }

    /**
     * This method finds the closest parent directory containing a file identified by name.
     *
     * @param string $fileName
     * @param string $directory
     *
     * @return null|string
     * @throws InvalidArgumentException
     */
    public function findParentByFile($fileName, $directory = null)
    {
        if (empty($fileName)) {
            throw new InvalidArgumentException('$fileName (value was ' . $fileName . ') must be set.', 1525785151);
        }

        $directory = $directory !== null ? $directory : getcwd();
        $hit       = null;

        while ($directory !== $this->environment->getRootDirectory()
            && $directory !== ''
            && $directory !== '/'
        ) {
            $stopFileFinder = $this->finderFactory->build();
            $stopFileFinder->in($directory)->files()->depth('== 1')->name('*' . $fileName . '*');
            if (count($stopFileFinder) !== 0) {
                $hit = $directory;
                break;
            }
            $directory = dirname($directory);
        }

        return $hit;
    }
}
