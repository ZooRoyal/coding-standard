<?php

namespace Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis;

use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSESLintAdapter;

class JSESLintCommand extends AbstractFixableToolCommand
{
    /**
     * JSESLintCommand constructor.
     *
     * @param JSESLintAdapter $toolAdapter
     */
    public function __construct(JSESLintAdapter $toolAdapter)
    {
        $this->toolAdapter = $toolAdapter;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sca:eslint');
        $this->setDescription('Run ESLint on JS files.');
        $this->setHelp(
            'This tool executes ESLINT on a certain set of JS files of this Project.'
            . 'Add a .dontSniffJS file to <JS-DIRECTORIES> that should be ignored.'
        );
        $this->setDefinition($this->buildInputDefinition());
    }
}
