<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;

class JSStyleLintCommand extends AbstractFixableToolCommand
{
    /**
     * JSStyleLintCommand constructor.
     *
     * @param JSStyleLintAdapter $toolAdapter
     */
    public function __construct(JSStyleLintAdapter $toolAdapter)
    {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:stylelint');
        $this->setDescription('Run StyleLint on Less files.');
        $this->setHelp('This tool executes STYLELINT on a certain set of Less files of this Project.'
            . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.');
        $this->setDefinition($this->buildInputDefinition());
    }
}
