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
class PostController extends Controller
{
    use Behaviour\JsonOutput;
    use Behaviour\Pagination;
    use Behaviour\Validator;

    /**
     * @Route("/posts/", name="posts")
     * @Method("GET")
     */
    public function listAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Post');

        $topic = $request->query->get('topic');
        if ($topic) {
            $topicRepo = $this->getDoctrine()->getRepository('AppBundle:Topic');
            $topic = $topicRepo->find($topic);
            if (!$topic) {
                throw $this->createNotFoundException('Could not find topic');
            }
        }

        $query = $repo->queryByNamespace($namespace, $topic);

        $pager = $this->createPager($request, $query);
        $collection = new Collection($pager, $this->get('transformer_post'), 'posts');
        $this->addPagination($request, $pager, $collection);

        return $this->generateJson($collection, $request);
    }

    /**
     * @Route("/posts/{id}", name="post")
     * @Method("GET")
     * @ParamConverter("post", class="AppBundle:Post", options={})
     */
    public function getAction(Request $request, $namespace, Entity\Post $post)
    {
        $resource = new Item($post, $this->get('transformer_post'), 'post');

        return $this->generateJson($resource, $request);
    }

    /**
     * @Route("/posts/{id}", name="post_delete")
     * @Method("DELETE")
     * @ParamConverter("post", class="AppBundle:Post", options={})
     */
    public function deleteAction(Request $request, $namespace, Entity\Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/posts/", name="post_create")
     * @Method("POST")
     */
    public function createAction(Request $request, $namespace)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Post');

        $post = $repo->createFromRequest($namespace, $this->getUser()->getUsername(), $request);

        $response = $this->validate($post);
        if ($response instanceof JsonResponse) {
            return $response;
        }

        $repo->save($post);

        $resource = new Item($post, $this->get('transformer_post'), 'post');

        return $this->generateJson($resource, $request, JsonResponse::HTTP_CREATED);
    }

    /**
     * @Route("/posts/{post}/ratings", name="post_rate")
     * @Method("POST")
     */
    public function rateAction(Request $request, Entity\Post $post)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:PostRating');
        $postRepo = $this->getDoctrine()->getRepository('AppBundle:Post');

        $rating = $repo->findOneBy([
            'userId' => $this->getUser()->getUsername(),
            'post' => $post,
        ]);

        $score = $request->request->getInt('score');

        if (!$rating) {
            $rating = $repo->create($this->getUser()->getUsername(), $post, $score);
        } else {
            $post->setScore($post->getScore() - $rating->getScore());
        }

        $rating->setScore($score);
        $post->setScore($post->getScore() + $rating->getScore());

        $response = $this->validate($rating);
        if ($response instanceof JsonResponse) {
            return $response;
        }

        $repo->save($rating);
        $postRepo->save($post);

        $resource = new Item($rating, $this->get('transformer_rating'), 'rating');

        return $this->generateJson($resource, $request, JsonResponse::HTTP_CREATED);
    }
}
