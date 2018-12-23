<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\EventsBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity Event (linked to DB table `events`)
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 *
 * @ORM\Table(name="events")
 * @ORM\Entity(repositoryClass="c975L\EventsBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * Event unique id
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * If Event is marked as suppressed
     * @var bool
     *
     * @ORM\Column(name="suppressed", type="boolean", options={"default":"0"})
     */
    protected $suppressed;

    /**
     * Title of the Event
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=128)
     */
    protected $title;

    /**
     * Slug for the Event
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="slug", type="string", length=128)
     */
    protected $slug;

    /**
     * Start date for the Event
     * @var DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * Start Time for the Event
     * @var DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    protected $startTime;

    /**
     * End date for the Event
     * @var DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * End time for the Event
     * @var DateTime
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    protected $endTime;

    /**
     * Place for the Event
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="place", type="string", length=256, nullable=true)
     */
    protected $place;

    /**
     * Description for the Event
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * Picture for the Event (not mapped in DB)
     * @var mixed
     *
     * @Assert\Image
     */
    protected $picture;

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set suppressed
     * @param bool
     * @return Event
     */
    public function setSuppressed($suppressed)
    {
        $this->suppressed = $suppressed;

        return $this;
    }

    /**
     * Get suppressed
     * @return bool
     */
    public function getSuppressed()
    {
        return $this->suppressed;
    }

    /**
     * Set title
     * @param string
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     * @param string
     * @return Event
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set startDate
     * @param DateTime
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set startTime
     * @param DateTime
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endDate
     * @param DateTime
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set endTime
     * @param DateTime
     * @return Event
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set place
     * @param string
     * @return Event
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set description
     * @param string
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set picture
     * @param string
     * @return Event
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }
}