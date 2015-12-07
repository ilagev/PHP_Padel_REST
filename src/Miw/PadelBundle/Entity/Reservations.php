<?php

namespace Miw\PadelBundle\Entity;

/**
 * Reservations
 */
class Reservations
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var \Miw\PadelBundle\Entity\Courts
     */
    private $court;

    /**
     * @var \Miw\PadelBundle\Entity\Users
     */
    private $user;


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
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return Reservations
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set court
     *
     * @param \Miw\PadelBundle\Entity\Courts $court
     *
     * @return Reservations
     */
    public function setCourt(\Miw\PadelBundle\Entity\Courts $court = null)
    {
        $this->court = $court;

        return $this;
    }

    /**
     * Get court
     *
     * @return \Miw\PadelBundle\Entity\Courts
     */
    public function getCourt()
    {
        return $this->court;
    }

    /**
     * Set user
     *
     * @param \Miw\PadelBundle\Entity\Users $user
     *
     * @return Reservations
     */
    public function setUser(\Miw\PadelBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Miw\PadelBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }
}

