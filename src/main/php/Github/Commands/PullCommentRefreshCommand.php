<?php

namespace Zooroyal\CodingStandard\Github\Commands;

use Github\Client;
use Github\Exception\MissingArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;

class PullCommentRefreshCommand extends Command
{
    private const USER_NAME = 'user_name';
    private const TOKEN = 'token';
    private const ORGANISATION = 'organisation';
    private const REPOSITORY = 'repository';
    private const PULL_NUMBER = 'pullNumber';
    private const COMMIT_ID = 'commitId';
    private const BODY = 'body';
    private const PATH = 'path';
    private const POSITION = 'position';
    private const ID = 'id';
    private Client $client;
    private CommentFilter $commentFilter;

    /**
     * GithubAddCommentCommand constructor.
     */
    public function __construct(Client $client, CommentFilter $commentFilter)
    {
        parent::__construct();

        $this->client = $client;
        $this->commentFilter = $commentFilter;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setName('pull:comment:refresh');
        $this->setDescription('Updates a comment to a file in Github pull requests. Creates it if it does not exist.');
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
                    new InputArgument(self::PULL_NUMBER, InputArgument::REQUIRED, 'ID of the pull request.'),
                    new InputArgument(self::COMMIT_ID, InputArgument::REQUIRED, 'ID of the commit.'),
                    new InputArgument(self::BODY, InputArgument::REQUIRED, 'Body of the comment.'),
                    new InputArgument(self::PATH, InputArgument::REQUIRED, 'File to comment.'),
                    new InputArgument(self::POSITION, InputArgument::OPTIONAL, 'Position in file', '1'),
                ]
            )
        );
    }

    /**
     * Executes the current command.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = $input->getArguments();

        $this->client->authenticate($arguments[self::USER_NAME], $arguments[self::TOKEN], Client::AUTH_HTTP_PASSWORD);

        [$ownComments, $staleComments] = $this->getCommentSets($arguments);

        $this->removeComments($staleComments, $arguments);

        if (count($staleComments) === count($ownComments)) {
            $this->createComment($arguments);
        } elseif (count($staleComments) + 1 === count($ownComments)) {
            $this->updateComment($ownComments, $staleComments, $arguments);
        }

        return 0;
    }

    /**
     * Fetches filtered sets of necessary comments
     *
     * @param array<string|bool|int|float|array|null> $arguments
     *
     * @return array<array<mixed>>
     */
    private function getCommentSets(array $arguments): array
    {
        $login = ($this->client->currentUser()->show())['login'];
        $comments = $this->client->pullRequest()->comments()->all(
            $arguments[self::ORGANISATION],
            $arguments[self::REPOSITORY],
            $arguments[self::PULL_NUMBER]
        );
        $ownComments = $this->commentFilter->filterForOwnComments($comments, $arguments[self::PATH], $login);
        $staleComments = $this->commentFilter->filterForStaleComments(
            $ownComments,
            $arguments[self::POSITION],
            $arguments[self::COMMIT_ID]
        );

        return [$ownComments, $staleComments];
    }

    /**
     * Removes comments from Github.
     *
     * @param array<mixed> $comments
     * @param array<string|bool|int|float|array|null> $arguments
     */
    private function removeComments(array $comments, array $arguments): void
    {
        foreach ($comments as $staleComment) {
            $this->client->pullRequest()->comments()->remove(
                $arguments[self::ORGANISATION],
                $arguments[self::REPOSITORY],
                $staleComment[self::ID]
            );
        }
    }

    /**
     * Creates an comment by the given arguments from commandline.
     *
     * @param array<string|bool|int|float|array|null> $arguments
     *
     * @throws MissingArgumentException
     */
    private function createComment(array $arguments): void
    {
        $parameter = [
            self::BODY => $arguments[self::BODY],
            'commit_id' => $arguments[self::COMMIT_ID],
            self::PATH => $arguments[self::PATH],
            self::POSITION => (int) $arguments[self::POSITION],
        ];

        $this->client->pullRequest()->comments()->create(
            $arguments[self::ORGANISATION],
            $arguments[self::REPOSITORY],
            $arguments[self::PULL_NUMBER],
            $parameter
        );
    }

    /**
     * Updates an comment which is not stale by the given arguments from commandline.
     *
     * @param array<mixed> $ownComments
     * @param array<mixed> $staleComments
     * @param array<string|bool|int|float|array|null> $arguments
     *
     * @throws MissingArgumentException
     */
    private function updateComment(array $ownComments, array $staleComments, array $arguments): void
    {
        [$currentComment] = array_values(
            array_udiff(
                $ownComments,
                $staleComments,
                static function ($item, $otherItem): int {
                    if ($item[self::ID] < $otherItem[self::ID]) {
                        return -1;
                    }
                    if ($item[self::ID] > $otherItem[self::ID]) {
                        return 1;
                    }

                    return 0;
                }
            )
        );
        $this->client->pullRequest()->comments()->update(
            $arguments[self::ORGANISATION],
            $arguments[self::REPOSITORY],
            $currentComment[self::ID],
            [self::BODY => $arguments[self::BODY]]
        );
    }
}
