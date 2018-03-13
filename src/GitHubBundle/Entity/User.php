<?php

namespace GitHubBundle\Entity;

use JMS\Serializer\Annotation\Type;

/**
 * Class User
 *
 * GitHub user representation
 *
 * @package GitHubBundle\Entity
 */
class User {

    /**
     * @Type("string")
     */
    private $login;

    /**
     * @Type("string")
     */
    private $avatarUrl;

    /**
     * @Type("string")
     */
    private $url;

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
