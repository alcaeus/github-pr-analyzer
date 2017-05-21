<?php declare(strict_types = 1);

namespace AppBundle\Reader\PullRequest;

use AppBundle\Document\PullRequest\Review as ReviewDocument;
use AppBundle\Reader\PaginatedReader;
use Github\Client;

final class Reviews extends PaginatedReader
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function read(string $username, string $repository, int $pullRequestNumber): array
    {
        $reviews = $this->paginate(function (int $page, int $perPage) use ($username, $repository, $pullRequestNumber) {
            return $this->client->pullRequest()->reviews()->all($username, $repository, $pullRequestNumber, ['per_page' => $perPage, 'page' => $page]);
        });

        return array_map(function (array $apiResponse) use ($username, $repository, $pullRequestNumber): ReviewDocument {
            $comments = (new Comments($this->client))->readForReview($username, $repository, $pullRequestNumber, (int) $apiResponse['id']);

            return ReviewDocument::createFromApiResponse($apiResponse, $comments);
        }, $reviews);
    }
}
