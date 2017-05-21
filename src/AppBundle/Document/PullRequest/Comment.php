<?php

namespace AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Comment
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
    private $body;

    /**
     * @ODM\Field(type="date")
     */
    private $createdAt;

    /**
     * @ODM\Field(type="date")
     */
    private $updatedAt;

    /**
     * @ODM\Field(type="hash")
     */
    private $rawResponse;

    public static function createFromApiResponse(array $response): self
    {
        $instance = new self();

        $instance->id = $response['id'];
        $instance->user = $response['user']['login'];
        $instance->body = $response['body'];
        $instance->createdAt = \DateTime::createFromFormat(DATE_ISO8601, $response['created_at']);
        $instance->updatedAt = \DateTime::createFromFormat(DATE_ISO8601, $response['updated_at']);
        $instance->rawResponse = $response;

        return $instance;
    }
}
