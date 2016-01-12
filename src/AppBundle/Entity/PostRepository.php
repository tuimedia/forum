<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostRepository extends EntityRepository
{
    public function create($owner, Topic $topic, $content, $isSticky = false)
    {
        $entity = new Post();
        $entity->setContent($content);
        $entity->setUserId($owner);
        $entity->setTopic($topic);
        $entity->setIsSticky($isSticky);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function createFromRequest($namespace, $user, Request $request)
    {
        if (!$request->request->has('topic')) {
            throw new NotFoundHttpException('Missing topic id');
        }

        $topic = $this->getEntityManager()->getRepository('AppBundle:Topic')->find($request->request->get('topic'));
        if (!$topic) {
            throw new NotFoundHttpException('Missing or invalid topic id');
        }

        $content = \AppBundle\StringCleaner::clean($request->request->get('content'));
        $post = $this->create($user, $topic, $content, $request->request->getBoolean('isSticky', false));

        return $post;
    }

    public function save(Post $entity)
    {
        $entity->setUpdated(date_create());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function queryByNamespace($namespace, Topic $topic = null, $order = ['isSticky' => 'DESC', 'created' => 'DESC'])
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.topic', 't')
            ->where('t.namespace = :namespace')
            ->setParameter('namespace', $namespace)
        ;

        if ($topic) {
            $query
                ->andWhere('p.topic = :topic')
                ->setParameter('topic', $topic)
            ;
        }

        foreach ($order as $key => $direction) {
            $query->addOrderBy('p.' . $key, $direction);
        }

        return $query->getQuery();
    }
}
