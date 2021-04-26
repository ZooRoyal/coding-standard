# Adding new information to your TerminalCommand

For information about adding your own TerminalCommand please refer to the [tutorial](HowToAddANewTool.md).

So you want to [implement a TerminalCommand](HowToAddANewTool.md) but sadly some
information you need to execute compile() in a meaningful way is not 
supplied by the coding-standard yet.

To fix that you need to do the following.

* Add a TerminalCommand interface
* Add a TerminalCommandDecorator
* Register your TerminalCommandDecorator
* Add a Trait

Let's say you want to add the information 'SuperCool' so it can be used 
while compiling a TerminalCommand.

## The TerminalCommand interface

First create an interface in 
`Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand` 
and name it appropriately. Let's call it `SuperInformedTerminalCommand.php`. 
This interface will tell a TerminalCommand what methods it needs to implement 
to be super informed.

```php
interface SuperInformedTerminalCommand extends TerminalCommand
{
    /**
     * Lets the command know what's up.
     *
     * @param string $information
     */
    public function addInformation(string $information): void;
}
```

Well that was easy. Let's continue with the decorator.

## The TerminalCommandDecorator

The TerminalCommandDecorator will actually inject your super cool information into 
TerminalCommands. Place your new TerminalCommandDecorator in 
`Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand` 
and call it `SuperCoolInformationDecorator.php`.

```php
// Use the TerminalCommandDecorator interface so we know what's what.
class SuperCoolInformationDecorator implements TerminalCommandDecorator
{
    // As the TerminalCommandDecorator is also a EventSubscriber we need to tell
    // the world what events we are interested in. If you want to know more 
    // about EventSubscribers I refer you to
    // https://symfony.com/doc/current/components/event_dispatcher.html
    public static function getSubscribedEvents(): array
    {
        // Please choose at least this event and bind it to the decorate method.
        return [AbstractToolCommand::EVENT_DECORATE_TERMINAL_COMMAND => ['decorate', 50]];
    }

    /**
     * This method decorates the TerminalCommand contained in the generic event. 
     * It will read information from the surrounding infrastructure or the command input.
     */
    public function decorate(GenericEvent $genericEvent): void
    {
        // Check if the TerminalCommand want's to be super informed. Not 
        // only is it rude to tell people things they don't want to hear. 
        // It will also crash the coding-standard
        $terminalCommand = $genericEvent->getSubject();

        if (!$terminalCommand instanceof SuperInformedTerminalCommand) {
            return;
        }

        // Now we actually inject the information. As we just implemented the
        // interface we can be sure that this method exists.
        $terminalCommand->addInformation('SuperCool');
    }

```

## Register the TerminalCommandDecorator

So you wrote your SuperCoolInformationDecorator. Nice! But you need to tell 
the coding-standard about its existence. That is as easy as writing a name 
on a list. Open `Zooroyal\CodingStandard\CommandLine\Factories
\EventDispatcherFactory`

```php
class EventDispatcherFactory
{
    /** @var array<string> */
    private const SUBSCRIBERS
        = [
            GitCommandPreconditionChecker::class,
            TerminalCommandPreconditionChecker::class,
            ExclusionDecorator::class,
            ExtensionDecorator::class,
            FixDecorator::class,
            TargetDecorator::class,
            VerbosityDecorator::class,
            SuperCoolInformationDecorator::class // Add Decorater to the list
        ];
    private Container $container;

// [...]
}
```

Well. That was easy.

## The Trait

Well... strictly speaking the trait is not needed for everything to work but 
to make the life of other developers easier you should definitely consider it.

The traits reside in `Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis
\Generic\TerminalCommand\Traits` so let's add your trait there. As names are 
important - to us. Coding-standard not so much - call your new trait 
`SuperInformedTrait.php`

```php
trait SuperInformedTrait
{
    // Choose a field to store the information it receives from your decorator.
    // Don't forget to give it some default value.
    // As traits are interpreter supported copy and paste, please do not try to
    // interact with anything outside of your trait. Please don't.
    protected string $information = '';

    // Now add a sensible default implementation for your information 
    // gathering method. The signatures is forced by your interface so don't 
    // look at me if you think it's wonky ^^
    /**
     * Receive super important information.
     *
     * @param string $information
     */
    final public function addInformation(string $information): void
    {
        $this->information = $information;
    }
}
```

