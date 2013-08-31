<?php

namespace SocialNetwork\Bundle\FollowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Follow
 *
 * @ORM\Table(name="follow")
 * @ORM\Entity
 */
class Follow
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User $following
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="following", referencedColumnName="id", nullable=FALSE)
     */
    private $following;

    /**
     * @var User $followed
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="followed", referencedColumnName="id", nullable=FALSE)
     */
    private $followed;

    /**
     * @var integer
     *
     * @ORM\Column(name="datetime", type="bigint")
     */
    private $datetime;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set following
     *
     * @param \stdClass $following
     * @return Follow
     */
    public function setFollowing($following)
    {
        $this->following = $following;
    
        return $this;
    }

    /**
     * Get following
     *
     * @return \stdClass 
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * Set followed
     *
     * @param \stdClass $followed
     * @return Follow
     */
    public function setFollowed($followed)
    {
        $this->followed = $followed;
    
        return $this;
    }

    /**
     * Get followed
     *
     * @return \stdClass 
     */
    public function getFollowed()
    {
        return $this->followed;
    }

    /**
     * Set datetime
     *
     * @param integer $datetime
     * @return Follow
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return integer 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }
}
