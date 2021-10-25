<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class FixableInputFacet implements ToolCommandInputFacet
{
    public const OPTION_FIX = 'fix';

    /**
     * This method returns the input definition needed to know if the user wants something to be fixed.
     */
    public function getInputDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption(
                    self::OPTION_FIX,
                    'f',
                    InputOption::VALUE_NONE,
                    'Runs tool to try to fix violations automagically.'
                ),
            ]
        );
    }
}
