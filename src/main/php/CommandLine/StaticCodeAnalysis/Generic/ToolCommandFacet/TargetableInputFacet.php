<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class TargetableInputFacet implements ToolCommandInputFacet
{
    /** @var string */
    public const OPTION_AUTO_TARGET = 'auto-target';
    /** @var string */
    public const OPTION_TARGET = 'target';

    /**
     * This method returns the input definition needed to know if the user wants the command to be focused on changed
     * files.
     */
    public function getInputDefinition(): InputDefinition
    {
        return new InputDefinition(
            [
                new InputOption(
                    self::OPTION_TARGET,
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Finds Files which have changed since the current branch parted from the target branch '
                    . 'only. The Value has to be a commit-ish.',
                    null
                ),
                new InputOption(
                    self::OPTION_AUTO_TARGET,
                    'a',
                    InputOption::VALUE_NONE,
                    'Finds Files which have changed since the current branch parted from the parent branch '
                    . 'only. It tries to find the parent branch by automagic.'
                ),
            ]
        );
    }
}
