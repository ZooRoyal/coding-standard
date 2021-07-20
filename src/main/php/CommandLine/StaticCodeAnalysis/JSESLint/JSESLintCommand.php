<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSESLint;

use DI\Annotation\Inject;
use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommand;

class JSESLintCommand extends FixingToolCommand
{
    /** @var string string */
    protected string $exclusionListToken = '.dontSniffJS';
    protected array $allowedFileEndings = ['js', 'ts', 'jsx', 'tsx'];
    private TerminalCommandFinder $terminalCommandFinder;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('sca:eslint');
        $this->setDescription('Run ESLint on JS files.');
        $this->setHelp(
            'This tool executes ESLINT on a certain set of JS files of this project.'
            . ' Add a .dontSniffJS file to <JS-DIRECTORIES> that should be ignored.'
        );
        $this->terminalCommandName = 'EsLint';
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->terminalCommandFinder->findTerminalCommand('eslint');
        } catch (TerminalCommandNotFoundException $exception) {
            $output->writeln('<info>EsLint could not be found. To use this sniff please refer to the README.md</info>');
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
