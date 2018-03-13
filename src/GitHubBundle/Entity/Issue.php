<?php

namespace GitHubBundle\Entity;

use JMS\Serializer\Annotation\Type;

/**
 * Class Issue
 *
 * GitHub issue representation
 *
 * @package GitHubBundle\Entity
 */
class Issue {

    /**
     * @Type("string")
     */
    private $url;

    /**
     * @Type("string")
     */
    private $title;

    /**
     * @Type("string")
     */
    private $state;

    /**
     * @Type("int")
     */
    private $number;

    /**
     * @Type("array")
     */
    private $labels;

    /**
     * @Type("string")
     */
    private $body;

    /**
     * @Type("int")
     */
    private $comments;

    /**
     * @Type("GitHubBundle\Entity\User")
     */
    private $user;

    /**
     * @Type("DateTime")
     */
    private $createdAt;

    /**
     * @Type("DateTime")
     */
    private $closedAt;

    public function getUrl()
    {
        return $this->url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getCommentsNumber()
    {
        return $this->comments;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

}
