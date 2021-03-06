<?php

namespace SocialNetwork\API\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupMember
 *
 * @ORM\Table(name="api_group_member")
 * @ORM\Entity
 */
class GroupMember
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
     * @ORM\Column(name="group_uid", type="string", length=255)
     */
    private $groupUid;

    /**
     * @var string
     *
     * @ORM\Column(name="member_uid", type="string", length=255)
     */
    private $memberUid;


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
     * Set groupUid
     *
     * @param string $groupUid
     * @return GroupMember
     */
    public function setGroupUid($groupUid)
    {
        $this->groupUid = $groupUid;
    
        return $this;
    }

    /**
     * Get groupUid
     *
     * @return string
     */
    public function getGroupUid()
    {
        return $this->groupUid;
    }

    /**
     * Set memberUid
     *
     * @param string $memberUid
     * @return GroupMember
     */
    public function setMemberUid($memberUid)
    {
        $this->memberUid = $memberUid;
    
        return $this;
    }

    /**
     * Get memberUid
     *
     * @return string 
     */
    public function getMemberUid()
    {
        return $this->memberUid;
    }
}
