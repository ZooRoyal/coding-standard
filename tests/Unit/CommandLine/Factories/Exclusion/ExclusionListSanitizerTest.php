<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\ExclusionListSanitizer;

class ExclusionListSanitizerTest extends TestCase
{
    private ExclusionListSanitizer $subject;

    protected function setUp(): void
    {
        $this->subject = new ExclusionListSanitizer();
    }

    /**
     * @test
     */
    public function sanitizeExclusionList()
    {
        $input = ['bla', 'bla', 'bla/blub', 'schackalacka', 'bum/schackalacka'];
        $expectedResult = ['bla', 'schackalacka', 'bum/schackalacka'];

        $result = $this->subject->sanitizeExclusionList($input);

        self::assertSame($expectedResult, $result);
    }
}
