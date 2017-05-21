<?php declare(strict_types = 1);

namespace AppBundle\Reader\PullRequest;

use AppBundle\Document\PullRequest\Comment;
use AppBundle\Reader\PaginatedReader;
use Github\Client;

final class Comments extends PaginatedReader
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function readForPullRequest(string $username, string $repository, int $pullRequestNumber): array
    {
        $comments = $this->paginate(function (int $page, int $perPage) use ($username, $repository, $pullRequestNumber) {
            // FIXME: API does not allow pagination for comments in pull requests - need to fix upstream
            return $this->client->pullRequest()->comments()->all($username, $repository, $pullRequestNumber, ['page' => $page, 'per_page' => $perPage]);
        });
        $nonReviewComments = array_filter($comments, function (array $response): bool {
            return ($response['pull_request_review_id'] ?? null) === null;
        });

        $issueComments = $this->paginate(function (int $page, int $perPage) use ($username, $repository, $pullRequestNumber) {
            $api = $this->client->issue()->comments();
            $api->setPerPage($perPage);
            return $api->all($username, $repository, $pullRequestNumber, $page);
        });

        return $this->createDocuments(array_merge($issueComments, $nonReviewComments));
    }

    public function readForReview(string $username, string $repository, int $pullRequestNumber, int $reviewId): array
    {
        // FIXME: API does not allow pagination for comments in reviews - need to fix upstream
        $comments = $this->client->pullRequest()->reviews()->comments($username, $repository, $pullRequestNumber, $reviewId);

        return $this->createDocuments($comments);
    }

    private function createDocuments(array $comments): array
    {
        return array_map([Comment::class, 'createFromApiResponse'], $comments);
    }
}
