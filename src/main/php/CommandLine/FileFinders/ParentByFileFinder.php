<?php

namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class ParentByFileFinder
{
    /** @var Environment */
    private $environment;
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Environment $environment, Filesystem $filesystem)
    {
        $this->environment = $environment;
        $this->filesystem = $filesystem;
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

        $directory = $directory ?? getcwd();
        $hit = null;

        while ($directory !== $this->environment->getRootDirectory()
            && $directory !== ''
            && $directory !== '.'
            && $directory !== '/'
        ) {
            if ($this->filesystem->exists($directory . '/' . $fileName)) {
                $hit = $directory;
                break;
            }
            $directory = dirname($directory);
        }

        return $hit;
    }
}
