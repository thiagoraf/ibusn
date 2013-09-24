<?php

namespace SocialNetwork\Bundle\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visitors
 *
 * @ORM\Table(name="visitors")
 * @ORM\Entity
 */
class Visitors
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
     * @var User $visited
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="visitor", referencedColumnName="id", nullable=FALSE)
     */
    private $visitor;

    /**
     * @var User $visited
     * @ORM\ManyToOne(targetEntity="SocialNetwork\API\Entity\User")
     * @ORM\JoinColumn(name="visited", referencedColumnName="id", nullable=FALSE)
     */
    private $visited;

    /**
     * @var integer
     *
     * @ORM\Column(name="date", type="bigint")
     */
    private $date;


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
     * Set visitor
     *
     * @param \stdClass $visitor
     * @return Visitors
     */
    public function setVisitor($visitor)
    {
        $this->visitor = $visitor;
    
        return $this;
    }

    /**
     * Get visitor
     *
     * @return \stdClass 
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * Set visited
     *
     * @param \stdClass $visited
     * @return Visitors
     */
    public function setVisited($visited)
    {
        $this->visited = $visited;
    
        return $this;
    }

    /**
     * Get visited
     *
     * @return \stdClass 
     */
    public function getVisited()
    {
        return $this->visited;
    }

    /**
     * Set date
     *
     * @param integer $date
     * @return Visitors
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return integer 
     */
    public function getDate()
    {
        return $this->date;
    }
}
