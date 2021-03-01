<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector;

use DI\Annotation\Inject;
use DI\Container;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;

class PHPCopyPasteDetectorCommand extends AbstractToolCommand
{
    /** @var string string */
    protected string $exclusionListToken = '.dontCopyPasteDetectPHP';

    /** @var array<string>  */
    protected array $allowedFileEndings = ['.php'];

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setName('sca:copy-paste-detect');
        $this->setDescription('Run PHP-CPD on PHP files.');
        $this->setHelp(
            'This tool executes PHP-CPD on a certain set of PHP files of this project. It ignores '
            . 'files which are in directories with a .dontCopyPasteDetectPHP file. Subdirectories are ignored too.'
        );
        $this->terminalCommandName = 'PHP Copy Paste Detector';
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
