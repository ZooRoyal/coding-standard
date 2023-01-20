<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\DecorateEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

class CodingStandardCommandEvent extends ConsoleEvent implements DecorateEvent
{
    /** @param array<string> $allowedFileEndings */
    public function __construct(
        ?Command $command,
        InputInterface $input,
        OutputInterface $output,
        private readonly TerminalCommand $terminalCommand,
        private readonly string $exclusionListToken,
        private readonly array $allowedFileEndings,
    ) {
        parent::__construct($command, $input, $output);
    }

    /** @return array<string> */
    public function getAllowedFileEndings(): array
    {
        return $this->allowedFileEndings;
    }

    public function getExclusionListToken(): string
    {
        return $this->exclusionListToken;
    }

    public function getTerminalCommand(): TerminalCommand
    {
        return $this->terminalCommand;
    }
}
