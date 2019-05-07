<?php

namespace Zooroyal\CodingStandard\Github\Commands;

use Github\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IssueCommentAddCommand extends Command
{
    /** @var Client */
    private $client;

    /**
     * GithubAddCommentCommand constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('issue:comment:add');
        $this->setDescription('Adds comment to Github Issue.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('token', InputArgument::REQUIRED, 'Access token or password for user.'),
                    new InputArgument('organisation', InputArgument::REQUIRED, 'The organisation og the repository.'),
                    new InputArgument('repository', InputArgument::REQUIRED, 'Repository of the issue.'),
                    new InputArgument('issue_id', InputArgument::REQUIRED, 'ID of the issue to add the command to.'),
                    new InputArgument('body', InputArgument::REQUIRED, 'Body of the comment.'),
                ]
            )
        );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void|null|0|int void null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getArgument('token');
        $organisation = $input->getArgument('organisation');
        $repository = $input->getArgument('repository');
        $issueId = $input->getArgument('issue_id');
        $parameter = ['body' => $input->getArgument('body')];

        $this->client->authenticate($token, '', Client::AUTH_URL_TOKEN);
        $this->client->issue()->comments()->create(
            $organisation,
            $repository,
            $issueId,
            $parameter
        );
    }
}
