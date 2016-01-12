<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PostRatingRepository extends EntityRepository
{
    public function create($owner, Post $post, $score)
    {
        $entity = new PostRating();
        $entity->setScore($score);
        $entity->setUserId($owner);
        $entity->setPost($post);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function save(PostRating $entity)
    {
        $entity->setUpdated(date_create());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }
}
