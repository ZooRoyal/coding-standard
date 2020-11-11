<?php

namespace Zooroyal\CodingStandard\Tests\System\CommandLine\Library;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class EnvironmentTest extends TestCase
{
    /** @var Environment */
    private $subject;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(Environment::class);
        $this->subject = $buildFragments['subject'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     *
     * It is only the same if coding-standard is installed as root package.
     * In project context these are different.
     */
    public function getRootDirectory()
    {
        $result = $this->subject->getRootDirectory();

        self::assertSame($this->subject->getPackageDirectory(), $result);
    }

    /**
     * @test
     */
    public function getPackageDirectory(): void
    {
        $result = $this->subject->getPackageDirectory();

        $readeMeFile = file_get_contents($result . '/README.md');
        $searchResult = strpos($readeMeFile, 'coding-standard');
        self::assertNotFalse($searchResult);
    }
}
