<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PostRepository")
 */
class Post
{
    use Behaviour\Timestamp;

    /**
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Assert\Length(min=2, max=20000, minMessage="post.length.too_short", maxMessage="post.length.too_long")
     */
    private $content;

    /**
     * @ORM\Column(name="is_sticky", type="boolean", nullable=false)
     * @Assert\Type(type="boolean", message="post.is_sticky.invalid")
     */
    private $isSticky = 0;

    /**
     * @ORM\Column(name="score", type="integer", nullable=false)
     */
    private $score = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Topic", inversedBy="posts", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true, name="topic_id")
     */
    private $topic;

    /**
     * @ORM\Column(name="user_id", type="string", nullable=false, length=32)
     */
    private $userId;

    /**
     * @ORM\OneToMany(targetEntity="Reply", mappedBy="post", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created" = "ASC"})
     */
    private $replies;

    /**
     * @ORM\OneToMany(targetEntity="PostRating", mappedBy="post", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    private $ratings;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ratings = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setCreated(date_create());
        $this->setUpdated(date_create());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTopic(Topic $topic = null)
    {
        $this->topic = $topic;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setIsSticky($isSticky)
    {
        $this->isSticky = $isSticky;
    }

    public function getIsSticky()
    {
        return $this->isSticky;
    }

    public function getReplies()
    {
        return $this->replies;
    }

    public function getRatings()
    {
        return $this->ratings;
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
