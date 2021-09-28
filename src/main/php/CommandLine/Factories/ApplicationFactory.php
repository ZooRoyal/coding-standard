<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\Checks\ForbiddenChangesCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\AllToolsCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSESLint\JSESLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer\PHPCodeSnifferCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector\PHPCopyPasteDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector\PHPMessDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint\PHPParallelLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\PHPStanCommand;

/**
 * Class ApplicationFactory
 * This class builds the Application of symfony/console. It will add all necessary commands. This class is meant to be
 * used as a PHP-DI factory.
 * If you want to add your own command just add it's class name to the array $commands.
 */
class ApplicationFactory
{
    /** @var array<string> */
    private const COMMANDS
        = [
            AllToolsCommand::class,
            FindFilesToCheckCommand::class,
            ForbiddenChangesCommand::class,
            PHPCodeSnifferCommand::class,
            PHPCopyPasteDetectorCommand::class,
            PHPParallelLintCommand::class,
            PHPMessDetectorCommand::class,
            PHPStanCommand::class,
            JSESLintCommand::class,
            JSStyleLintCommand::class,
        ];
    private EventDispatcherInterface $eventDispatcher;
    private Container $container;

    /**
     * ApplicationFactory constructor.
     */
    public function __construct(
        Container $container,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Builds an instance of Application and sets it up with EventDispatcher and coding-standard Commands.
     */
    public function build(): Application
    {
        $application = new Application('Coding-Standard');
        $application->setDispatcher($this->eventDispatcher);

        foreach (self::COMMANDS as $command) {
            $application->add($this->container->get($command));
        }

        return $application;
    }
}
