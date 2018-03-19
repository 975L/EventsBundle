<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use c975L\EventsBundle\Repository\EventRepository;

/**
 * Event
 *
 * @ORM\Table(name="events")
 * @ORM\Entity(repositoryClass="c975L\EventsBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="suppressed", type="boolean")
     */
    protected $suppressed;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=128)
     */
    protected $title;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="slug", type="string", length=128)
     */
    protected $slug;

    /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * @ORM\Column(name="start_time", type="datetime")
     */
    protected $startTime;

    /**
     * @var \DateTime
     * @ORM\Column(name="end_date", type="datetime")
     */
    protected $endDate;

    /**
     * @ORM\Column(name="end_time", type="datetime")
     */
    protected $endTime;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="place", type="string", length=256)
     */
    protected $place;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @Assert\Image
     */
    protected $picture;


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
     * Set suppressed
     *
     * @return Event
     */
    public function setSuppressed($suppressed)
    {
        $this->suppressed = $suppressed;

        return $this;
    }

    /**
     * Get suppressed
     *
     * @return string
     */
    public function getSuppressed()
    {
        return $this->suppressed;
    }

    /**
     * Set title
     *
     * @return Events
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @return Events
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set startDate
     *
     * @return Events
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set startTime
     *
     * @return Events
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endDate
     *
     * @return Events
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set endTime
     *
     * @return Events
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set place
     *
     * @return Events
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set description
     *
     * @return Events
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return User
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }
}
