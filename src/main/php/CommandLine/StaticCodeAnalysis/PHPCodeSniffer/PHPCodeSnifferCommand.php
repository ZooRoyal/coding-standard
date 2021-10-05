<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer;

use DI\Annotation\Inject;
use DI\Container;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommand;

class PHPCodeSnifferCommand extends FixingToolCommand
{
    /** @var string string */
    protected string $exclusionListToken = '.dontSniffPHP';
    /** @var array<string>  */
    protected array $allowedFileEndings = ['php'];

    public function configure(): void
    {
        parent::configure();
        $this->setName('sca:sniff');
        $this->setDescription('Run PHP-CS on PHP files.');
        $this->setHelp(
            'This tool executes PHP-CS on a certain set of PHP files of this project. '
            . 'It ignores files which are in directories with a .dontSniffPHP file. Subdirectories are ignored too.'
        );
        $this->terminalCommandName = 'PHP Code Sniffer';
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
