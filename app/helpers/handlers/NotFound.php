<?php

namespace app\Helpers\handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface as Container;
use Slim\Handlers\AbstractHandler;
use Slim\Http\Body;
use UnexpectedValueException;

class NotFound extends AbstractHandler
{
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response $response
     */
    public function __invoke(Request $request, Response $response)
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonNotFoundOutput();
                break;

            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlNotFoundOutput();
                break;

            case 'text/html':
                $output = $this->renderHtmlNotFoundOutput();
                break;

            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response->withStatus(404)
            ->withHeader('Content-Type', $contentType)
            ->withBody($body);
    }

    /**
     * Return a response for application/json content not found
     *
     * @return string
     */
    protected function renderJsonNotFoundOutput()
    {
        return '{"message":"Not found"}';
    }

    /**
     * Return a response for xml content not found
     *
     * @return string
     */
    protected function renderXmlNotFoundOutput()
    {
        return '<root><message>Not found</message></root>';
    }

    /**
     * @return string
     */
    protected function renderHtmlNotFoundOutput()
    {
        $view = $this->container->get('view');
        if ($view->exists('theme::404')) {
            return $view->render('theme::404');
        }
        return $view->render('handlers/404');
    }
}
