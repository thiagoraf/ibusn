<?php

namespace SocialNetwork\Bundle\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserGroup
 *
 * @ORM\Table(name="user_group")
 * @ORM\Entity
 */
class UserGroup
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
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer")
     */
    private $idUser;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_group", type="integer")
     */
    private $idGroup;

    /**
     * @var integer
     *
     * @ORM\Column(name="date_registred", type="bigint")
     */
    private $dateRegistred;


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
     * Set idUser
     *
     * @param integer $idUser
     * @return UserGroup
     */
    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;
    
        return $this;
    }

    /**
     * Get idUser
     *
     * @return integer 
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * Set idGroup
     *
     * @param integer $idGroup
     * @return UserGroup
     */
    public function setIdGroup($idGroup)
    {
        $this->idGroup = $idGroup;
    
        return $this;
    }

    /**
     * Get idGroup
     *
     * @return integer 
     */
    public function getIdGroup()
    {
        return $this->idGroup;
    }

    /**
     * Set dateRegistred
     *
     * @param integer $dateRegistred
     * @return UserGroup
     */
    public function setDateRegistred($dateRegistred)
    {
        $this->dateRegistred = $dateRegistred;
    
        return $this;
    }

    /**
     * Get dateRegistred
     *
     * @return integer 
     */
    public function getDateRegistred()
    {
        return $this->dateRegistred;
    }
}
