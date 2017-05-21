<?php

namespace AppBundle\Document;

use AppBundle\Document\PullRequest\Comment;
use AppBundle\Document\PullRequest\Review;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="pull_requests")
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class PullRequest
{
    /**
     * @ODM\Id(type="int", strategy="NONE")
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     */
    private $repository;

    /**
     * @ODM\Field(type="int")
     */
    private $number;

    /**
     * @ODM\Field(type="string")
     */
    private $url;

    /**
     * @ODM\Field(type="string")
     */
    private $state;

    /**
     * @ODM\Field(type="string")
     */
    private $title;

    /**
     * @ODM\Field(type="string")
     */
    private $body;

    /**
     * @ODM\Field(type="string")
     */
    private $milestone;

    /**
     * @ODM\Field(type="date")
     */
    private $createdAt;

    /**
     * @ODM\Field(type="date")
     */
    private $updatedAt;

    /**
     * @ODM\Field(type="date")
     */
    private $closedAt;

    /**
     * @ODM\Field(type="date")
     */
    private $mergedAt;

    /**
     * @ODM\Field(type="string")
     */
    private $head;

    /**
     * @ODM\Field(type="string")
     */
    private $base;

    /**
     * @ODM\Field(type="string")
     */
    private $user;

    /**
     * @ODM\Field(type="bool")
     */
    private $merged = false;

    /**
     * @ODM\Field(type="string")
     */
    private $mergedBy;

    /**
     * @ODM\Field(type="int")
     */
    private $numComments = 0;

    /**
     * @ODM\Field(type="int")
     */
    private $numReviewComments = 0;

    /**
     * @ODM\Field(type="int")
     */
    private $numCommits = 0;

    /**
     * @ODM\Field(type="int")
     */
    private $linesAdded = 0;

    /**
     * @ODM\Field(type="int")
     */
    private $linesRemoved = 0;

    /**
     * @ODM\Field(type="int")
     */
    private $changedFiles = 0;

    /**
     * @ODM\EmbedMany(targetDocument=Comment::class, strategy="atomicSetArray")
     */
    private $comments;

    /**
     * @ODM\EmbedMany(targetDocument=Review::class, strategy="atomicSetArray")
     */
    private $reviews;

    /**
     * @ODM\Hash
     */
    private $rawResponse = [];

    private function __construct()
    {
    }

    public static function createFromApiResponse(array $response, array $reviews, array $comments): self
    {
        $instance = new static();

        $apiMappings = [
            'id' => 'id',
            'number' => 'number',
            'url' => 'url',
            'state' => 'state',
            'locked' => 'locked',
            'title' => 'title',
            'body' => 'body',
            'merged' => 'merged',
            'comments' => 'numComments',
            'review_comments' => 'numReviewComments',
            'commits' => 'numCommits',
            'additions' => 'linesAdded',
            'deletions' => 'linesRemoved',
            'changed_files' => 'changedFiles',
        ];

        $dates = [
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'closed_at' => 'closedAt',
            'merged_at' => 'mergedAt',
        ];

        foreach ($apiMappings as $apiField => $field) {
            $instance->$field = $response[$apiField];
        }

        foreach ($dates as $apiField => $field) {
            if (!$response[$apiField]) {
                continue;
            }

            $instance->$field = \DateTime::createFromFormat(DATE_ISO8601, $response[$apiField]);
        }

        $instance->repository = $response['base']['repo']['full_name'];
        $instance->user = $response['user']['login'];
        $instance->head = $response['head']['ref'];
        $instance->base = $response['base']['ref'];

        if (is_array($response['merged_by'])) {
            $instance->mergedBy = $response['merged_by']['login'];
        }

        if (is_array($response['milestone'])) {
            $instance->milestone = $response['milestone']['title'];
        }

        $instance->rawResponse = $response;

        array_map([$instance, 'addReview'], $reviews);
        array_map([$instance, 'addComment'], $comments);

        return $instance;
    }

    private function addReview(Review $review): void
    {
        $this->reviews[] = $review;
    }

    private function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }
}
