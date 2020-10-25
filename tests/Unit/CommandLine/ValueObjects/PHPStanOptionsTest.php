<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ValueObjects;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\PHPStanInputOptions;

class PHPStanOptionsTest extends TestCase
{

    /** @var array[] */
    private $inputOptions;

    protected function setUp()
    {
        $this->inputOptions = [
            [
                'name' => 'target',
                'short' => 't',
                'option' => InputOption::VALUE_REQUIRED,
                'description' => 'Finds files which have changed since the current branch parted from the target branch only.
                 The value has to be a commit-ish.',
            ],
            [
                'name' => 'auto-target',
                'short' => 'a',
                'option' => InputOption::VALUE_NONE,
                'description' => 'Finds files which have changed since the current branch parted from the parent branch only. 
                It tries to find the parent branch by automagic.',
            ],
            [
                'name' => 'process-isolation',
                'short' => 'p',
                'option' => InputOption::VALUE_NONE,
                'description' => 'Runs all checks in separate processes. Slow but not as resource hungry.',
            ],
        ];
    }

    /**
     * @test
     */
    public function compareGettingInputOptions()
    {
        $inputOptions = new PHPStanInputOptions();

        self::assertEquals($this->inputOptions, $inputOptions->getInputOptions());
    }
}
