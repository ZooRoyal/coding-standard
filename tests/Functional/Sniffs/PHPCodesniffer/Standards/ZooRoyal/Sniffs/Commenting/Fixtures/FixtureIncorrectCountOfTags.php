<?php
namespace Zooroyal\CodingStandard\Tests\Functional\Sniffs\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use Exception;
use RuntimeException;

class FixtureIncorrectCountOfTags
{
    public function bla(): void
    {
        throw new RuntimeException();
    }

    /**
     * @throws Exception
     */
    public function blub(): void
    {
        if (mt_rand(0, 1) == 1) {
            throw new Exception();
        }
        throw new RuntimeException();
    }
}
