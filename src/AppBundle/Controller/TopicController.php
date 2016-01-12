<?php

namespace AppBundle\Controller;

use AppBundle\Entity;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/{namespace}")
 */
class TopicController extends Controller
{
    use Behaviour\JsonOutput;
    use Behaviour\Pagination;
    use Behaviour\Validator;

    /**
     * @Route("/topics/", name="topics")
     * @Method("GET")
     */
    public function listAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Topic');

        $reference = $request->query->get('reference');
        $parent = $request->query->get('parent');
        if ($parent) {
            $parent = $repo->find($parent);
            if (!$parent) {
                throw $this->createNotFoundException('Could not find parent topic');
            }
        }

        $query = $repo->queryByNamespace($namespace, $reference, $parent, ['created' => 'DESC']);

        $pager = $this->createPager($request, $query);
        $collection = new Collection($pager, $this->get('transformer_topic'), 'topics');
        $this->addPagination($request, $pager, $collection);

        return $this->generateJson($collection, $request);
    }

    /**
     * @Route("/topics/{id}", name="topic")
     * @Method("GET")
     * @ParamConverter("topic", class="AppBundle:Topic", options={})
     */
    public function getAction(Request $request, $namespace, Entity\Topic $topic)
    {
        $resource = new Item($topic, $this->get('transformer_topic'), 'topic');

        return $this->generateJson($resource, $request);
    }

    /**
     * @Route("/topics/{id}", name="topic_delete")
     * @Method("DELETE")
     * @ParamConverter("topic", class="AppBundle:Topic", options={})
     */
    public function deleteAction(Request $request, $namespace, Entity\Topic $topic)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($topic);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/topics/", name="topic_create")
     * @Method("POST")
     */
    public function createAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Topic');

        $topic = $repo->createFromRequest($namespace, $this->getUser()->getUsername(), $request);

        $response = $this->validate($topic);
        if ($response instanceof JsonResponse) {
            return $response;
        }

        $repo->save($topic);

        $resource = new Item($topic, $this->get('transformer_topic'), 'topic');

        return $this->generateJson($resource, $request, JsonResponse::HTTP_CREATED);
    }
}
