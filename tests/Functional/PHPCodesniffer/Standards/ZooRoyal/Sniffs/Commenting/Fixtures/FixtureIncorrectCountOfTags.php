<?php
namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use Exception;
use RuntimeException;

class FixtureIncorrectCountOfTags
{
    public function bla()
    {
        throw new RuntimeException();
    }

    /**
     * @throws Exception
     */
    public function blub()
    {
        if (mt_rand(0, 1) === 1) {
            throw new Exception();
        }
        throw new RuntimeException();
    }
}
