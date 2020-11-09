<?php

namespace Zooroyal\CodingStandard\Github\Library;

class CommentFilter
{
    /**
     * Finds all stale comments which need to be deleted.
     *
     * @param array  $comments
     * @param int    $position
     * @param string $commitId
     *
     * @return array
     */
    public function filterForStaleComments(array $comments, int $position, string $commitId) : array
    {
        $staleComments = array_filter(
            $comments,
            static function ($item) use ($position, $commitId) {
                return ($item['position'] === $position || $item['original_position'] === $position)
                    && $item['original_commit_id'] !== $commitId;
            }
        );

        return array_values($staleComments);
    }

    /**
     * Finds all comments which are written by the current user.
     *
     * @param array       $comments
     * @param string      $path
     * @param string      $login
     *
     * @return array
     */
    public function filterForOwnComments(array $comments, string $path, string $login) : array
    {
        $result = array_filter(
            $comments,
            static function ($item) use ($path, $login) {
                return $item['path'] === $path
                    && $item['user']['login'] === $login;
            }
        );

        return array_values($result);
    }
}
