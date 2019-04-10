<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;

class PHPCodeSnifferCommand extends AbstractFixableToolCommand
{
    /**
     * PHPCodeSnifferCommand constructor.
     *
     * @param PHPCodeSnifferAdapter $toolAdapter
     */
    public function __construct(PHPCodeSnifferAdapter $toolAdapter)
    {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:sniff');
        $this->setDescription('Run PHP-CS on PHP files.');
        $this->setHelp(
            'This tool executes PHP-CS on a certain set of PHP files of this Project. '
            . 'It ignores files which are in directories with a .dontSniffPHP file. Subdirectories are ignored too.'
        );
        $this->setDefinition($this->buildInputDefinition());
    }
}
