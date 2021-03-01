<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\Github\Library;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;

class CommentFilterTest extends TestCase
{
    private CommentFilter $subject;

    protected function setUp(): void
    {
        $this->subject = new CommentFilter();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @return array<string,array<array<array<string,int|string>>|int|string>> */
    public function filterForStaleCommentsDoesExactlyThatDataProvider(): array
    {
        $position = 1;
        $commitId = '1asdasd2';

        $positionsData = [
            'position' => $position,
            'original_position' => $position,
            'original_commit_id' => $commitId,
        ];
        $onePositionWrongData = [
            'position' => $position,
            'original_position' => 123,
            'original_commit_id' => $commitId,
        ];
        $allWrongData = [
            'position' => 432,
            'original_position' => 123,
            'original_commit_id' => 'qweasdqwe121212',
        ];

        return [
            'alles richtig' => [[$allWrongData, $positionsData], $position, '5', [$positionsData]],
            'eine position falsch' => [
                [$allWrongData, $onePositionWrongData],
                $position,
                '5',
                [$onePositionWrongData],
            ],
            'positions falsch' => [[$allWrongData, $positionsData], 5, '5', []],
            'original_commit_id falsch' => [[$allWrongData, $positionsData], $position, $commitId, []],
        ];
    }

    /**
     * @test
     * @dataProvider filterForStaleCommentsDoesExactlyThatDataProvider
     *
     * @param array<array<mixed>> $mockedComments
     * @param array<array<mixed>> $expectedResult
     */
    public function filterForStaleCommentsDoesExactlyThat(
        array $mockedComments,
        int $mockedPosition,
        string $mockedCommitId,
        array $expectedResult,
    ): void {
        $result = $this->subject->filterForStaleComments($mockedComments, $mockedPosition, $mockedCommitId);

        self::assertSame($expectedResult, $result);
    }

    /** @return array<string,array<array<array<string,array<string,string>|string>>|string>> */
    public function filterForOwnCommentsDoesExactlyThatDataProvider(): array
    {
        $path = 'asdasd';
        $login = '1asdasd2';

        $positionData = ['path' => $path, 'user' => ['login' => $login]];
        $allWrongData = ['path' => 'asdasd1233', 'user' => ['login' => '123as21as12as']];

        return [
            'alles richtig' => [[$allWrongData, $positionData], $path, $login, [$positionData]],
            'login falsch' => [[$allWrongData, $positionData], $path, 'qqqq', []],
            'path falsch' => [[$allWrongData, $positionData], 'wwww', $login, []],
        ];
    }

    /**
     * @test
     * @dataProvider filterForOwnCommentsDoesExactlyThatDataProvider
     *
     * @param array<array<mixed>> $mockedComments
     * @param array<array<mixed>> $expectedResult
     */
    public function filterForOwnCommentsDoesExactlyThat(
        array $mockedComments,
        string $mockedPath,
        string $mockedUser,
        array $expectedResult,
    ): void {
        $result = $this->subject->filterForOwnComments($mockedComments, $mockedPath, $mockedUser);

        self::assertSame($expectedResult, $result);
    }
}
