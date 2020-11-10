<?php
namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use Exception;
use RuntimeException;

class FixtureCorrectCountOfTags
{
    /**
     * @throws RuntimeException
     */
    public function bla()
    {
        throw new RuntimeException();
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function blub()
    {
        throw new Exception();
    }

    /**
     * That was short
     *
     * @throws RuntimeException
     */
    public function foo()
    {
        $this->bla();
    }
}
