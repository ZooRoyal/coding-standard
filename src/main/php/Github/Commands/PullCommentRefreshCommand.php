<?php

namespace Zooroyal\CodingStandard\Github\Commands;

use Github\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;

class PullCommentRefreshCommand extends Command
{
    /** @var Client */
    private $client;
    /** @var CommentFilter */
    private $commentFilter;

    /**
     * GithubAddCommentCommand constructor.
     *
     * @param Client        $client
     * @param CommentFilter $commentFilter
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
    protected function configure()
    {
        $this->setName('pull:comment:refresh');
        $this->setDescription('Updates a comment to a file in Github pull requests. Creates it if it does not exist.');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('token', InputArgument::REQUIRED, 'Access token or password for user.'),
                    new InputArgument('organisation', InputArgument::REQUIRED, 'The organisation og the repository.'),
                    new InputArgument('repository', InputArgument::REQUIRED, 'Repository of the issue.'),
                    new InputArgument('pullNumber', InputArgument::REQUIRED, 'ID of the pull request.'),
                    new InputArgument('commitId', InputArgument::REQUIRED, 'ID of the commit.'),
                    new InputArgument('body', InputArgument::REQUIRED, 'Body of the comment.'),
                    new InputArgument('path', InputArgument::REQUIRED, 'File to comment.'),
                    new InputArgument('position', InputArgument::OPTIONAL, 'Position in file', '1'),
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
        $arguments = $input->getArguments();

        $this->client->authenticate($arguments['token'], '', Client::AUTH_URL_TOKEN);

        list($ownComments, $staleComments) = $this->getCommentSets($arguments);

        $this->removeComments($staleComments, $arguments);

        if (count($staleComments) === count($ownComments)) {
            $this->createComment($arguments);
        } elseif (count($staleComments) + 1 === count($ownComments)) {
            $this->updateComment($ownComments, $staleComments, $arguments);
        }
    }

    /**
     * Fetches filtered sets of necessary comments
     *
     * @param array $arguments
     *
     * @return array
     */
    private function getCommentSets(array $arguments) : array
    {
        $login = ($this->client->currentUser()->show())['login'];
        $comments = $this->client->pullRequest()->comments()->all(
            $arguments['organisation'],
            $arguments['repository'],
            $arguments['pullNumber']
        );
        $ownComments = $this->commentFilter->filterForOwnComments($comments, $arguments['path'], $login);
        $staleComments = $this->commentFilter->filterForStaleComments(
            $ownComments,
            $arguments['position'],
            $arguments['commitId']
        );
        return [$ownComments, $staleComments];
    }

    /**
     * Removes comments from Github.
     *
     * @param array $comments
     * @param array $arguments
     *
     * @return void
     */
    private function removeComments(array $comments, array $arguments)
    {
        foreach ($comments as $staleComment) {
            $this->client->pullRequest()->comments()->remove(
                $arguments['organisation'],
                $arguments['repository'],
                $staleComment['id']
            );
        }
    }

    /**
     * Creates an comment by the given arguments from commandline.
     *
     * @param array $arguments
     *
     * @return void
     */
    private function createComment(array $arguments)
    {
        $parameter = [
            'body' => $arguments['body'],
            'commit_id' => $arguments['commitId'],
            'path' => $arguments['path'],
            'position' => (int) $arguments['position'],
        ];

        $this->client->pullRequest()->comments()->create(
            $arguments['organisation'],
            $arguments['repository'],
            $arguments['pullNumber'],
            $parameter
        );
    }

    /**
     * Updates an comment which is not stale by the given arguments from commandline.
     *
     * @param array $ownComments
     * @param array $staleComments
     * @param array $arguments
     *
     * @return void
     */
    private function updateComment(array $ownComments, array $staleComments, array $arguments)
    {
        list($currentComment) = array_values(
            array_udiff(
                $ownComments,
                $staleComments,
                static function ($item, $otherItem) {
                    if ($item['id'] < $otherItem['id']) {
                        return -1;
                    }
                    if ($item['id'] > $otherItem['id']) {
                        return 1;
                    }
                    return 0;
                }
            )
        );
        $this->client->pullRequest()->comments()->update(
            $arguments['organisation'],
            $arguments['repository'],
            $currentComment['id'],
            ['body' => $arguments['body']]
        );
    }
}
