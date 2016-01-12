<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReplyRepository extends EntityRepository
{
    public function create($owner, Post $post, $content)
    {
        $entity = new Reply();
        $entity->setContent($content);
        $entity->setUserId($owner);
        $entity->setPost($post);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function save(Reply $entity)
    {
        $entity->setUpdated(date_create());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function createFromRequest($namespace, $user, Request $request)
    {
        if (!$request->request->has('post')) {
            throw new NotFoundHttpException('Missing post id');
        }

        $post = $this->getEntityManager()->getRepository('AppBundle:Post')->find($request->request->get('post'));
        if (!$post) {
            throw new NotFoundHttpException('Missing or invalid post id');
        }

        $content = \AppBundle\StringCleaner::clean($request->request->get('content'));
        $reply = $this->create($user, $post, $content);

        return $reply;
    }

    public function queryByNamespace($namespace, Post $post = null, $order = ['created' => 'DESC'])
    {
        $query = $this->createQueryBuilder('r')
            ->join('r.post', 'p')
            ->join('p.topic', 't')
            ->where('t.namespace = :namespace')
            ->setParameter('namespace', $namespace)
        ;

        if ($post) {
            $query
                ->andWhere('r.post = :post')
                ->setParameter('post', $post)
            ;
        }

        foreach ($order as $key => $direction) {
            $query->addOrderBy('r.' . $key, $direction);
        }

        return $query->getQuery();
    }
}
