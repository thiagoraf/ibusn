<?php

namespace SocialNetwork\Bundle\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chat
 *
 * @ORM\Table(name="global_chat")
 * @ORM\Entity
 */
class Chat
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
     * @var User $userId
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="datetime", type="bigint")
     */
    private $datetime;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;


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
     * Set userId
     *
     * @param \stdClass $userId
     * @return Chat
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return \stdClass 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set datetime
     *
     * @param integer $datetime
     * @return Chat
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

    /**
     * Set message
     *
     * @param string $message
     * @return Chat
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }
}
