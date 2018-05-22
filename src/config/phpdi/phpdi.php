<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

return [
    InputInterface::class  => DI\get(ArgvInput::class),
    OutputInterface::class => DI\get(ConsoleOutput::class),
];
