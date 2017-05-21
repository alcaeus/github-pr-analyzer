<?php declare(strict_types = 1);

namespace AppBundle\Reader;

use AppBundle\Document\PullRequest as PullRequestDocument;
use Github\Client;

final class PullRequest
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function read(string $username, string $repository, int $number): PullRequestDocument
    {
        $pullRequest = $this->client->pullRequests()->show($username, $repository, (string) $number);

        $reviews = (new PullRequest\Reviews($this->client))->read($username, $repository, $number);
        $comments = (new PullRequest\Comments($this->client))->readForPullRequest($username, $repository, $number);

        return PullRequestDocument::createFromApiResponse($pullRequest, $reviews, $comments);
    }
}
