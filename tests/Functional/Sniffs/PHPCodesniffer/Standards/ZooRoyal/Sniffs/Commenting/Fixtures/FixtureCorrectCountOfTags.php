<?php
namespace Zooroyal\CodingStandard\Tests\Functional\Sniffs\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use Exception;
use RuntimeException;

class FixtureCorrectCountOfTags
{
    /**
     * @throws RuntimeException
     */
    public function bla(): void
    {
        throw new RuntimeException();
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function blub(): void
    {
        throw new Exception();
    }

    /**
     * That was short
     *
     * @throws RuntimeException
     */
    public function foo(): void
    {
        $this->bla();
    }
}
