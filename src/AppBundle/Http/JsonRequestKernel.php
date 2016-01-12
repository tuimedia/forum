<?php
namespace AppBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// Load the body of incoming json requests into the request object
class JsonRequestKernel implements HttpKernelInterface
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : []);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $request->setRequestFormat('json');
        }

        return $response = $this->app->handle($request, $type, $catch);
    }

    public function terminate(Request $request, Response $response)
    {
        $this->app->terminate($request, $response);
    }
}
