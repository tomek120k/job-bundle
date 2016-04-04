<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;

use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Job implements JobInterface
{
    /**
     * @var string
     * @Type("string")
     */
    protected $ticket;

    /**
     * @var string
     * @Type("string")
     */
    protected $type;

    /**
     * @var Status
     * @Type("string")
     */
    protected $status;

    /**
     * @var array|null
     */
    protected $parameters;

    /**
     * @var double
     * @Type("double")
     */
    protected $processingTime;

    /**
     * @var \DateTime
     * @Type("DateTime")
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @Type("DateTime")
     */
    protected $terminatedAt;

    /**
     * @var mixed|null
     */
    protected $response;

    /**
     * @var ArrayCollection
     * @Type("array<Abc\Bundle\JobBundle\Model\Schedule>")
     */
    protected $schedules;

    /**
     * @param string|null $type
     * @param array|null  $parameters
     */
    public function __construct($type = null, $parameters = null)
    {
        $this->schedules      = new ArrayCollection();
        $this->status         = Status::REQUESTED();
        $this->processingTime = 0;
        $this->type           = $type;
        $this->parameters     = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters($parameters = null)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse($response = null)
    {
        return $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function createSchedule($type, $expression)
    {
        return new Schedule($type, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchedules()
    {
        return 0 !== $this->schedules->count();
    }

    /**
     * {@inheritdoc}
     */
    public function addSchedule(BaseScheduleInterface $schedule)
    {
        $this->schedules->add($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSchedule(BaseScheduleInterface $schedule)
    {
       $this->schedules->removeElement($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSchedules()
    {
        foreach($this->getSchedules() as $schedule)
        {
            $this->removeSchedule($schedule);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setTerminatedAt(\DateTime $terminatedAt)
    {
        $this->terminatedAt = $terminatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getTerminatedAt()
    {
        return $this->terminatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessingTime($processingTime)
    {
        $this->processingTime = $processingTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessingTime()
    {
        return $this->processingTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutionTime()
    {
        $terminationTimestamp = $this->terminatedAt == null ? time() : $this->terminatedAt->format('U');

        return $terminationTimestamp - $this->createdAt->format('U');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getTicket();
    }

    /**
     * Override clone in order to avoid duplicating entries in Doctrine
     */
    public function __clone()
    {
        $this->ticket = null;
    }
}