<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint;

use DI\Annotation\Inject;
use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommand;

class JSStyleLintCommand extends FixingToolCommand
{
    /** @var string string */
    protected string $exclusionListToken = '.dontSniffLESS';
    protected array $allowedFileEndings = ['css', 'scss', 'sass', 'less'];
    private TerminalCommandFinder $terminalCommandFinder;

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
        } catch (TerminalCommandNotFoundException $exception) {
            $output->writeln('<info>Stylelint could not be found. To use this sniff please refer to the README.md</info>');
            return 0;
        }
        return parent::execute($input, $output);
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @param Container $container
     * @param TerminalCommandFinder $terminalCommandFinder
     *
     * @see http://php-di.org/doc/annotations.html
     *
     * @Inject
     */
    public function injectDependenciesCommand(Container $container, TerminalCommandFinder $terminalCommandFinder): void
    {
        $this->terminalCommandFinder = $terminalCommandFinder;
        $this->terminalCommand = $container->make(TerminalCommand::class);
    }
}
