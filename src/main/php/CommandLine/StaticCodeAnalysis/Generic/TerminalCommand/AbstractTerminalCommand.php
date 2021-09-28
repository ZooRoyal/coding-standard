<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use DI\Annotation\Inject;
use Exception;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTerminalCommand implements TerminalCommand
{
    /** @var array<string> */
    protected array $commandParts = [];
    protected string $command = '';
    protected OutputInterface $output;
    private bool $compiled = false;

    /**
     * Returns the command as sting. This string is supposed to work as input to a
     * *NIX terminal.
     */
    public function __toString(): string
    {
        if (!$this->compiled) {
            $this->runCompilation();
        }
        return $this->command;
    }

    /**
     * Returns the command as array. Every part of the command is in its own array item.
     *
     * @example find ./ -name "asd" -> ['find', './', '-name', '"asd"']
     *
     * @return array<string>
     */
    public function toArray(): array
    {
        if (!$this->compiled) {
            $this->runCompilation();
        }
        return $this->commandParts;
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @see http://php-di.org/doc/annotations.html
     *
     * @Inject
     */
    public function injectDependenciesAbstractTerminalCommand(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * This method must implement the compilation of the command. Technically it has one shoot in setting the protected
     * fields $command and $commandParts.
     */
    abstract protected function compile(): void;

    /**
     * This method is used to execute all the stuff we need to do after successful command compilation.
     */
    private function postCompile(): void
    {
        $this->output->writeln(
            '<info>Compiled TerminalCommand to following string</info>' . PHP_EOL . $this->command . PHP_EOL,
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );
    }

    /**
     * Triggers the compilation implemented in the child class.
     *
     * @throws NoUsefulCommandFoundException
     * @throws RuntimeException
     */
    private function runCompilation(): void
    {
        $this->compiled = true;
        try {
            $this->compile();
        } catch (NoUsefulCommandFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new RuntimeException('Something went wrong compiling a command', 1616426291, $exception);
        }
        $this->postCompile();
    }
}
