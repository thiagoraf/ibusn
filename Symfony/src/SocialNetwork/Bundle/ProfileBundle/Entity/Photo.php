<?php

namespace SocialNetwork\Bundle\ProfileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo
 *
 * @ORM\Table(name="photo")
 * @ORM\Entity
 */
class Photo
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
     * @var Album $album
     * @ORM\ManyToOne(targetEntity="SocialNetwork\Bundle\ProfileBundle\Entity\Album")
     * @ORM\JoinColumn(name="album", referencedColumnName="id", nullable=FALSE)
     */
    private $album;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="text")
     */
    private $source;

    /**
     * @var integer
     *
     * @ORM\Column(name="timestamp", type="bigint")
     */
    private $timestamp;


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
     * Set album
     *
     * @param \stdClass $album
     * @return Photo
     */
    public function setAlbum($album)
    {
        $this->album = $album;
    
        return $this;
    }

    /**
     * Get album
     *
     * @return \stdClass 
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Set source
     *
     * @param integer $source
     * @return Photo
     */
    public function setSource($source)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get source
     *
     * @return integer 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set sourceId
     *
     * @param string $sourceId
     * @return Photo
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
    
        return $this;
    }

    /**
     * Get sourceId
     *
     * @return string 
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set timestamp
     *
     * @param integer $timestamp
     * @return Photo
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    
        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
