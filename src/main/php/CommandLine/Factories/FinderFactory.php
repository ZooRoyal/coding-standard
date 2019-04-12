<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use Symfony\Component\Finder\Finder;

/**
 * This class creates Finder instances for better use with dependency injection
 */
class FinderFactory
{
    /**
     * This method returns a new Finder instance.
     *
     * @return Finder
     */
    public function build()
    {
        $result = new Finder();
        if (method_exists($result, 'useBestAdapter')) {
            $result->useBestAdapter();
        }
        $result->ignoreDotFiles(false);
        $result->ignoreVCS(false);

        return $result;
    }
}
