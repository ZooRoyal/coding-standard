<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint;

use DI\Annotation\Inject;
use DI\Container;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TargetableToolsCommand;

class PHPParallelLintCommand extends TargetableToolsCommand
{
    protected string $exclusionListToken = '.dontLintPHP';
    /** @var array<string>  */
    protected array $allowedFileEndings = ['php'];

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('sca:parallel-lint');
        $this->setDescription('Run Parallel-Lint on PHP files.');
        $this->setHelp(
            'This tool executes Parallel-Lint on a certain set of PHP files of this project. It '
            . 'ignores files which are in directories with a .dontLintPHP file. Subdirectories are ignored too.'
        );
        $this->terminalCommandName = 'PHP Parallel Lint';
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @see http://php-di.org/doc/annotations.html
     *
     * @Inject
     */
    public function injectDependenciesCommand(Container $container): void
    {
        $this->terminalCommand = $container->make(TerminalCommand::class);
    }
}
