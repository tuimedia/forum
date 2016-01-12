<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TopicRepository extends EntityRepository
{
    public function createFromRequest($namespace, $user, Request $request)
    {
        $parent = $request->request->get('parentId');
        if ($parent) {
            $parent = $this->find($parent);
            if (!$parent) {
                throw new NotFoundHttpException('Could not find parent topic');
            }
        }

        $topic = $this->create($namespace, $user, $request->request->get('title'), $request->request->get('externalReference'), $parent);

        return $topic;
    }

    public function create($namespace, $owner, $title, $externalReference = null, Topic $parent = null)
    {
        $entity = new Topic();
        $entity->setNamespace($namespace);
        $entity->setTitle($title);
        $entity->setUserId($owner);
        $entity->setExternalReference($externalReference);
        $entity->setParent($parent);

        return $entity;
    }

    public function save(Topic $entity)
    {
        $entity->setUpdated(date_create());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    public function queryByNamespace($namespace, $reference = null, Topic $parent = null, $order = ['t.created' => 'DESC'])
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.namespace = :namespace')
            ->setParameter('namespace', $namespace)
        ;

        if ($parent) {
            $query
                ->andWhere('t.parent = :parent')
                ->setParameter('parent', $parent)
            ;
        }

        if ($reference) {
            $query
                ->andWhere('t.externalReference = :reference')
                ->setParameter('reference', $reference)
            ;
        }

        foreach ($order as $key => $direction) {
            $query->addOrderBy('t.' . $key, $direction);
        }

        return $query->getQuery();
    }
}
