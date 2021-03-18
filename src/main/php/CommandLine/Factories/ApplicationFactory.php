<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\Checks\ForbiddenChangesCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\AllToolsCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\JSESLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPCodeSnifferCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPCopyPasteDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPMessDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPParallelLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPStanCommand;

/**
 * Class ApplicationFactory
 * This class builds the Application of symfony/console. It will add all necessary commands. This class is meant to be
 * used as a PHP-DI factory.
 * If you want to add your own command just add it's class name to the array $commands.
 */
class ApplicationFactory
{
    private EventDispatcherInterface $eventDispatcher;
    private Container $container;
    /** @var array<string> */
    private const COMMANDS = [
        PHPParallelLintCommand::class,
        PHPCodeSnifferCommand::class,
        PHPStanCommand::class,
        FindFilesToCheckCommand::class,
        PHPMessDetectorCommand::class,
        PHPCopyPasteDetectorCommand::class,
        JSESLintCommand::class,
        JSStyleLintCommand::class,
        AllToolsCommand::class,
        ForbiddenChangesCommand::class,
    ];

    /**
     * ApplicationFactory constructor.
     *
     * @param Container                $container
     * @param EventDispatcherInterface $eventDispatcher
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
        $application = new Application();
        $application->setDispatcher($this->eventDispatcher);

        foreach (self::COMMANDS as $command) {
            $application->add($this->container->get($command));
        }

        return $application;
    }
}
