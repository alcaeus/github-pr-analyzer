<?php declare(strict_types = 1);

namespace AppBundle\Document\AggregationResult;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\QueryResultDocument
 */
final class UserStatistics
{
    /**
     * @ODM\Field(type="string", name="_id")
     */
    private $user;

    /**
     * @ODM\Field(type="int")
     */
    private $numPullRequests;

    /**
     * @ODM\Field(type="int")
     */
    private $numComments;

    /**
     * @ODM\Field(type="int")
     */
    private $numApprovals;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    private $numRequestChanges;

    private function __construct()
    {
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getNumPullRequests(): int
    {
        return $this->numPullRequests;
    }

    public function getNumComments(): int
    {
        return $this->numComments;
    }

    public function getNumApprovals(): int
    {
        return $this->numApprovals;
    }

    public function getNumRequestChanges(): int
    {
        return $this->numRequestChanges;
    }
}
