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
     * @ORM\Column(name="description", type="text", length=65000, nullable=true)
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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set suppressed
     * @param bool
     * @return Event
     */
    public function setSuppressed(?bool $suppressed)
    {
        $this->suppressed = $suppressed;

        return $this;
    }

    /**
     * Get suppressed
     * @return bool
     */
    public function getSuppressed(): ?bool
    {
        return $this->suppressed;
    }

    /**
     * Set title
     * @param string
     * @return Event
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set slug
     * @param string
     * @return Event
     */
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Set startDate
     * @param DateTime
     * @return Event
     */
    public function setStartDate(?DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     * @return DateTime
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * Set startTime
     * @param DateTime
     * @return Event
     */
    public function setStartTime(?DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     * @return DateTime
     */
    public function getStartTime(): ?DateTime
    {
        return $this->startTime;
    }

    /**
     * Set endDate
     * @param DateTime
     * @return Event
     */
    public function setEndDate(?DateTime $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     * @return DateTime
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * Set endTime
     * @param DateTime
     * @return Event
     */
    public function setEndTime(?DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     * @return DateTime
     */
    public function getEndTime(): ?DateTime
    {
        return $this->endTime;
    }

    /**
     * Set place
     * @param string
     * @return Event
     */
    public function setPlace(?string $place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     * @return string
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }

    /**
     * Set description
     * @param string
     * @return Event
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set picture
     * @param string
     * @return Event
     */
    public function setPicture(?string $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     * @return string
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }
}
