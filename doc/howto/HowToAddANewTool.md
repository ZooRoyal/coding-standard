# How to add a new static code analysis tool

## Gather information

First of all you need to gather some information about your new tool.

1. Is the tool able to only work on files you provide by a command-line 
   parameter? \
   For example Parallel Lint is able to only work on certain files.
   ```bash
   vendor/bin/parallel-lint aFileIWantToCheck.php
   ```
2. Is the tool able to fix violations in a file? \
   For example ESLint is able to fix violations.
   ```bash
   eslint -f aFileIWantToFix.js
   ```

## Add your code

Let's say you want to add your new tool 'SuperCoolTool' to the coding-standard

1. Create a new namespace 
   `Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\SuperCoolTool`.
2. Create two new Classes in your new namespace: `SuperCoolToolCommand` and `TerminalCommand`.
3. Register your new command.

The next paragraphs will guid you through that process.

### Implement SuperCoolToolCommand

This class stores some vital information about the SuperCoolTool. The 
coding-standard will use this information to integrate your tool in its cli.

For now it's best to just have a look at this example implementation.

There are 3 base classes to chose from, depending on what capabilities of
your tool you want to expose to the user.

* FixingToolCommand  - provides fixing capabilities and can target files
* TargetableToolsCommand - can target files
* AbstractToolCommand - neither of the above

We are going to assume a FixingToolCommand for our SuperCoolTool - its SuperCool after all!

```php
class SuperCoolToolCommand extends FixingToolCommand 
{
    // What file makes your tool ignore a directory
    protected string $exclusionListToken = '.dontBeSuperCool'; 
    // Do you only want to check for certain file Types? If not leave empty
    protected array $allowedFileEndings = ['sc'];

    // Add some basic information about your tool
    public function configure()
    {
        // Do this... always.
        parent::configure();

        // Choose the command to use for your tool
        $this->setName('sca:supercool');

        // Choose a description
        $this->setDescription('Run the SuperCool tool');

        // Write a helpful message for your enduser
        $this->setHelp(
            'This tool executes the SuperCoolTool on a certain set of SC files '
            . 'of this project. It ignores files which are in directories with a '
            . '.dontBeSuperCool file. Subdirectories are ignored too.'
        );

        // Choose by which name your tool will be referenced by the coding-standard
        $this->terminalCommandName = 'The Super Cool Tool';
    }

    /**
     * This method accepts all dependencies needed to use this class properly.
     * It's annotated for use with PHP-DI.
     *
     * @param Container $container
     *
     * @see http://php-di.org/doc/annotations.html
     *
     * @Inject
     */
    public function injectDependenciesCommand(Container $container): void
    {
        // Create an instance of your TerminalCommand and store it in this property. 
        // You may use the container like so but it really doesn't matter as long as
        // an instance of your TerminalCommand will be stored in this property
        $this->terminalCommand = $container->make(TerminalCommand::class);
    }
}

```

### Implement your TerminalCommand

The coding-standard will inject your TerminalCommand with important 
information about the user input and context of the system, but you have to tell it what information you would like to get. For this reason we are using interfaces. \
In case of your SuperCoolTool we will get information about the files it 
should be checking and the fixing mode.

Let's see...
```php

// To make your life more easy just use AbstractTerminalCommand as base for your new class.
// Now we tell the coding-standard which kind of TerminalCommand this is.
// It is a FixingTerminalCommand and TargetableTerminalCommand
class TerminalCommand extends AbstractTerminalCommand implements FixingTerminalCommand, TargetableTerminalCommand
{
    // So you don't need to hassle with setters and such, simply use the 
    // provided traits. The coding-standard provides a trait for every
    // TerminalCommand interface. 
    use TargetableTrait, FixingTrait;

    // The TargetableTrait will create a field named $targetedFiles. The 
    // FixingTrait will create a field named $fixingMode. They will be set 
    // by higher magic.
    // For more information about available traits and interfaces you may 
    // need to read their respectivsource code
    
    /**
     * This method must implement the compilation of the command. Technically it has 
     * one shot in setting the protected fields $command and $commandParts.
     */
    protected function compile(): void
    {
        // Now it's your turn to use the information supplied to the TerminalCommand
        // to generate a string and an array which may be thrown to the command line
        // to execute your SuperCoolTool. As this may be tricky feel free to have a look
        // at the already implemented tools.

        // Don't forget to set the result to the appropriate fields
        $this->command = 'supercool -t -o -o -l';
        $this->commandParts = ['supercool', '-t', '-o', '-o', '-l'];
    }
}
```

### Register your new Tool

You implemented and tested `SuperCoolToolCommand` and `TerminalCommand`... now what?

To let coding-standard know there is a fancy new tool in town the final task left to do is registering it to the system. Thank god that's easy

Open the class `Zooroyal\CodingStandard\CommandLine\ApplicationLifeCycle\ApplicationFactory`.

```php
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
            SuperCoolToolCommand::class, // Just add your class to the list
        ];

    // [...]
}
```

BAM! It's done! Now you can call your tool from command-line
```bash 
src/bin/coding-standard sca:supercool # from your dev environment
vendor/bin/coding-standard sca:supercool # if installed as a library
```
