<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reply
 *
 * @ORM\Table(name="reply")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ReplyRepository")
 */
class Reply
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
     * @Assert\Length(min=2, max=20000, minMessage="reply.length.too_short", maxMessage="reply.length.too_long")
     */
    private $content;

    /**
     * @ORM\Column(name="score", type="integer", nullable=false)
     */
    private $score = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="replies", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true, name="post_id")
     */
    private $post;

    /**
     * @ORM\Column(name="user_id", type="string", nullable=false, length=32)
     */
    private $userId;

    public function __construct()
    {
        $this->setCreated(date_create());
        $this->setUpdated(date_create());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPost(Post $post = null)
    {
        $this->post = $post;
    }

    public function getPost()
    {
        return $this->post;
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

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
