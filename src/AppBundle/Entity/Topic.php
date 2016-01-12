<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Topic
 *
 * @ORM\Table(name="topic", indexes={
 *      @ORM\Index(name="namespace_idx", columns={"namespace"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TopicRepository")
 */
class Topic
{
    use Behaviour\Timestamp;

    /**
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(name="namespace", type="string", length=32, nullable=false)
     * @Assert\Type(type="string", message="namespace.type")
     * @Assert\NotBlank(message="namespace.blank")
     * @Assert\Length(min=3, max=32, minMessage="namespace.length", maxMessage="namespace.length")
     */
    private $namespace;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Assert\Type(type="string", message="topic.title.type")
     * @Assert\NotBlank(message="topic.title.blank")
     * @Assert\Length(min=1, max=255, minMessage="topic.title.length", maxMessage="topic.title.length")
     */
    private $title;

    /**
     * @ORM\Column(name="external_ref", type="string", length=255, nullable=true)
     * @Assert\Type(type="string", message="topic.reference.type")
     * @Assert\Length(min=1, max=255, minMessage="topic.reference.length", maxMessage="topic.reference.length")
     */
    private $externalReference;

    /**
     * @ORM\Column(name="user_id", type="string", nullable=false, length=32)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Topic", inversedBy="children", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true, name="parent_id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Topic", mappedBy="parent", fetch="EXTRA_LAZY")
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="topic", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"isSticky" = "DESC", "created" = "DESC"})
     */
    private $posts;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->posts = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setCreated(date_create());
        $this->setUpdated(date_create());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setParent(Topic $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setExternalReference($externalReference)
    {
        $this->externalReference = $externalReference;
    }

    public function getExternalReference()
    {
        return $this->externalReference;
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
