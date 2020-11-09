<?php
namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use RuntimeException;

class FixtureCorrectComments
{
    /** @var string */
    private $field = 'bla';

    /**
     * Asd
     *
     * @param $firstParameter
     * @param $secondParameter
     *
     * @return int
     * @throws RuntimeException
     */
    public function addParameters($firstParameter = null, $secondParameter = null)
    {
        if ($firstParameter && $secondParameter) {
            /** @var RuntimeException $exception */
            $exception = new RuntimeException($this->field);
            throw $exception;
        }

        return $firstParameter + $secondParameter;
    }

    /**
     * @test
     */
    public function fakeTestMethod()
    {
        return 'bla';
    }
}
