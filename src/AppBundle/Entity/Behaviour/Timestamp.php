<?php
namespace AppBundle\Entity\Behaviour;

trait Timestamp
{
    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $created;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     */
    private $updated;

    public function setCreated(\DateTime $created)
    {
        $this->created = clone $created;
    }

    public function getCreated()
    {
        return clone $this->created;
    }

    public function setUpdated(\DateTime $updated = null)
    {
        $this->updated = is_object($updated) ? clone $updated : null;
    }

    public function getUpdated()
    {
        return $this->updated ? clone $this->updated : null;
    }
}
