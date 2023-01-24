<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector;

use DI\Attribute\Inject;
use DI\Container;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TargetableToolsCommand;

class PHPMessDetectorCommand extends TargetableToolsCommand
{
    /** @var string string */
    protected string $exclusionListToken = '.dontMessDetectPHP';

    /** @var array<string> */
    protected array $allowedFileEndings = ['php'];

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('sca:mess-detect');
        $this->setDescription('Run PHP-MD on PHP files.');
        $this->setHelp(
            'This tool executes PHP-MD on a certain set of PHP files of this project. It ignores files which are in '
            . 'directories with a .dontMessDetectPHP file. Subdirectories are ignored too.',
        );
        $this->terminalCommandName = 'PHP Mess Detector';
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @see http://php-di.org/doc/annotations.html
     */
    #[Inject]
    public function injectDependenciesCommand(Container $container): void
    {
        $this->terminalCommand = $container->make(TerminalCommand::class);
    }
}
