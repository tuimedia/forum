<?php
namespace AppBundle\Controller\Behaviour;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

trait Pagination
{
    protected function createPager(Request $request, $query)
    {
        $pageSize = $request->query->getInt('limit', 10);
        if ($pageSize < 1 || $pageSize > 100) {
            return new JsonResponse(['error' => 'Invalid page size. Valid range: 1-100.'], 500);
        }
        $page = $request->query->getInt('page', 1);

        $adapter = new DoctrineORMAdapter($query);
        $pager = (new Pagerfanta($adapter))
            ->setMaxPerPage($pageSize)
            ->setCurrentPage($page)
        ;

        return $pager;
    }

    protected function addPagination(Request $request, Pagerfanta $pager, $resource)
    {
        $route = $request->attributes->get('_route');
        $params = $request->attributes->get('_route_params');
        $params = array_merge($params, $request->query->all());

        $resource->setMetaValue('page', $pager->getCurrentPage());
        $resource->setMetaValue('count', $pager->getNbResults());
        $resource->setMetaValue('nextPage', null);
        $resource->setMetaValue('previousPage', null);
        $resource->setMetaValue('next', null);
        $resource->setMetaValue('previous', null);

        if ($pager->hasNextPage()) {
            $resource->setMetaValue('next', $this->generateUrl($route, array_replace($params, [
                'page' => $pager->getNextPage(),
                'limit' => $pager->getMaxPerPage(),
            ]), true));
            $resource->setMetaValue('nextPage', $pager->getNextPage());
        }

        if ($pager->hasPreviousPage()) {
            $resource->setMetaValue('previous', $this->generateUrl($route, array_replace($params, [
                'page' => $pager->getPreviousPage(),
                'limit' => $pager->getMaxPerPage(),
                ]), true));
            $resource->setMetaValue('previousPage', $pager->getPreviousPage());
        }
    }
}
