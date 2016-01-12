<?php

namespace AppBundle\Controller;

use AppBundle\Entity;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/{namespace}")
 */
class ReplyController extends Controller
{
    use Behaviour\JsonOutput;
    use Behaviour\Pagination;
    use Behaviour\Validator;

    /**
     * @Route("/replies/", name="replies")
     * @Method("GET")
     */
    public function listAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Reply');

        $post = $request->query->get('post');
        if ($post) {
            $postRepo = $this->getDoctrine()->getRepository('AppBundle:Post');
            $post = $postRepo->find($post);
            if (!$post) {
                throw $this->createNotFoundException('Could not find post');
            }
        }

        $query = $repo->queryByNamespace($namespace, $post);

        $pager = $this->createPager($request, $query);
        $collection = new Collection($pager, $this->get('transformer_reply'), 'replies');
        $this->addPagination($request, $pager, $collection);

        return $this->generateJson($collection, $request);
    }

    /**
     * @Route("/replies/{id}", name="reply_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $namespace, Entity\Reply $reply)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($reply);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/replies/", name="reply_create")
     * @Method("POST")
     */
    public function createAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Reply');

        $reply = $repo->createFromRequest($namespace, $this->getUser()->getUsername(), $request);

        $response = $this->validate($reply);
        if ($response instanceof JsonResponse) {
            return $response;
        }

        $repo->save($reply);

        $resource = new Item($reply, $this->get('transformer_reply'), 'reply');

        return $this->generateJson($resource, $request, JsonResponse::HTTP_CREATED);
    }
}
