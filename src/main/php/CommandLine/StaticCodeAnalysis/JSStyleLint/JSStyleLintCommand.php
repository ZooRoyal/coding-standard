<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint;

use DI\Attribute\Inject;
use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\NpmAppFinder\NpmCommandFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\NpmAppFinder\NpmCommandNotFoundException;

class JSStyleLintCommand extends FixingToolCommand
{
    protected string $exclusionListToken = '.dontSniffLESS';

    /** @var array<string> */
    protected array $allowedFileEndings = ['css', 'scss', 'sass', 'less'];
    private NpmCommandFinder $terminalCommandFinder;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('sca:stylelint');
        $this->setDescription('Run StyleLint on Less files.');
        $this->setHelp(
            'This tool executes STYLELINT on a certain set of Less files of this project.'
            . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.'
        );
        $this->terminalCommandName = 'StyleLint';
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->terminalCommandFinder->findTerminalCommand('stylelint');
        } catch (NpmCommandNotFoundException) {
            $output->writeln('<info>Stylelint could not be found. To use this sniff please refer to the README.md</info>');
            return 0;
        }

        return parent::execute($input, $output);
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @see http://php-di.org/doc/annotations.html
     */
    #[Inject]
    public function injectDependenciesCommand(Container $container, NpmCommandFinder $terminalCommandFinder): void
    {
        $this->terminalCommandFinder = $terminalCommandFinder;
        $this->terminalCommand = $container->make(TerminalCommand::class);
    }
}
