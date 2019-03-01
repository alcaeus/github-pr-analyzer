<?php

namespace AppBundle\Document\PullRequest;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Review
{
    /**
     * @ODM\Field(type="int")
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     */
    private $user;

    /**
     * @ODM\Field(type="string")
     */
    private $state;

    /**
     * @ODM\Field(type="string")
     */
    private $body;

    /**
     * @ODM\EmbedMany(targetDocument=Comment::class, strategy="setArray")
     */
    private $comments;

    /**
     * @ODM\Field(type="hash")
     */
    private $rawResponse;

    public static function createFromApiResponse(array $response, array $comments): self
    {
        $instance = new self();

        $instance->id = $response['id'];
        $instance->user = $response['user']['login'];
        $instance->state = $response['state'];
        $instance->body = $response['body'];

        array_map([$instance, 'addComment'], $comments);

        $instance->rawResponse = $response;

        return $instance;
    }

    private function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }
}
