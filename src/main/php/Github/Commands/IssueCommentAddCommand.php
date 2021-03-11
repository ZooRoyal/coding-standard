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
    private Client $client;

    /** @var string */
    private const USER_NAME = 'user_name';
    /** @var string */
    private const ISSUE_ID = 'issue_id';
    /** @var string */
    private const REPOSITORY = 'repository';
    /** @var string */
    private const ORGANISATION = 'organisation';
    /** @var string */
    private const TOKEN = 'token';
    /** @var string */
    private const BODY = 'body';

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
    protected function configure(): void
    {
        $this->setName('issue:comment:add');
        $this->setDescription('Adds comment to Github Issue.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument(self::TOKEN, InputArgument::REQUIRED, 'Access token or password for user.'),
                    new InputArgument(self::USER_NAME, InputArgument::REQUIRED, 'The Github username'),
                    new InputArgument(
                        self::ORGANISATION,
                        InputArgument::REQUIRED,
                        'The organisation of the repository.'
                    ),
                    new InputArgument(self::REPOSITORY, InputArgument::REQUIRED, 'Repository of the issue.'),
                    new InputArgument(
                        self::ISSUE_ID,
                        InputArgument::REQUIRED,
                        'ID of the issue to add the command to.'
                    ),
                    new InputArgument(self::BODY, InputArgument::REQUIRED, 'Body of the comment.'),
                ]
            )
        );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $token        = $input->getArgument(self::TOKEN);
        $organisation = $input->getArgument(self::ORGANISATION);
        $repository   = $input->getArgument(self::REPOSITORY);
        $issueId      = (int) $input->getArgument(self::ISSUE_ID);
        $username     = $input->getArgument(self::USER_NAME);
        $parameter    = [self::BODY => $input->getArgument(self::BODY)];

        $this->client->authenticate($username, $token, Client::AUTH_HTTP_PASSWORD);
        $this->client->issue()->comments()->create(
            $organisation,
            $repository,
            $issueId,
            $parameter
        );

        return 0;
    }
}
