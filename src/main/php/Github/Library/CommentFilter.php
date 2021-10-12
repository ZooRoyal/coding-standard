<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Github\Library;

class CommentFilter
{
    /**
     * Finds all stale comments which need to be deleted.
     *
     * @param array<mixed> $comments
     *
     * @return array<mixed>
     */
    public function filterForStaleComments(array $comments, int $position, string $commitId): array
    {
        $staleComments = array_filter(
            $comments,
            static function ($item) use ($position, $commitId): bool {
                return ($item['position'] === $position || $item['original_position'] === $position)
                    && $item['original_commit_id'] !== $commitId;
            }
        );

        return array_values($staleComments);
    }

    /**
     * Finds all comments which are written by the current user.
     *
     * @param array<mixed> $comments
     *
     * @return array<mixed>
     */
    public function filterForOwnComments(array $comments, string $path, string $login): array
    {
        $result = array_filter(
            $comments,
            static function ($item) use ($path, $login): bool {
                return $item['path'] === $path
                    && $item['user']['login'] === $login;
            }
        );

        return array_values($result);
    }
}
