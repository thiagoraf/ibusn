<?php

namespace SocialNetwork\Bundle\FriendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Friends
 *
 * @ORM\Table(name="friends")
 * @ORM\Entity
 */
class Friends
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
     * @var User $userRequest
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="id_user_request", referencedColumnName="id", nullable=FALSE)
     */
    private $idUserRequest;

    /**
     * @var User $userResponse
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="id_user_response", referencedColumnName="id", nullable=FALSE)
     */
    private $idUserResponse;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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
     * Set idUserRequest
     *
     * @param \stdClass $idUserRequest
     * @return Friends
     */
    public function setIdUserRequest($idUserRequest)
    {
        $this->idUserRequest = $idUserRequest;
    
        return $this;
    }

    /**
     * Get idUserRequest
     *
     * @return \stdClass 
     */
    public function getIdUserRequest()
    {
        return $this->idUserRequest;
    }

    /**
     * Set idUserResponse
     *
     * @param \stdClass $idUserResponse
     * @return Friends
     */
    public function setIdUserResponse($idUserResponse)
    {
        $this->idUserResponse = $idUserResponse;
    
        return $this;
    }

    /**
     * Get idUserResponse
     *
     * @return \stdClass 
     */
    public function getIdUserResponse()
    {
        return $this->idUserResponse;
    }


    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
