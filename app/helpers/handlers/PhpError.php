<?php

namespace app\helpers\handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Body;
use UnexpectedValueException;

/**
 * Error Handler
 * @package app\helpers\handlers
 */
class PhpError extends AbstractError
{
    /**
     * @param Request $request
     * @param Response $response
     * @param \Throwable $error
     * @return Response $response
     */
    public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($error);
                break;

            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlErrorMessage($error);
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorMessage($error);
                break;

            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        $this->writeToErrorLog($error, 'php_error');

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
            ->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * @param \Throwable $error
     * @return mixed
     */
    protected function renderHtmlErrorMessage(\Throwable $error)
    {

        if ($this->debug) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($error);

            while ($error = $error->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($error);
            }

            return $this->container->get('view')->render('handlers/error', ['html' => $html]);
        }

        return $this->container->get('view')->render('handlers/500');
    }

    /**
     * @param \Throwable $error
     * @return string
     */
    protected function renderHtmlException(\Throwable $error)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($error));

        if (($code = $error->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $error->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $error->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $error->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $error->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * @param \Throwable $error
     * @return string
     */
    protected function renderJsonErrorMessage(\Throwable $error)
    {
        $json = [
            'message' => 'Application Error',
        ];

        if ($this->debug) {
            $json['exception'] = [];

            do {
                $json['exception'][] = [
                    'type' => get_class($error),
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'trace' => explode("\n", $error->getTraceAsString()),
                ];
            } while ($error = $error->getPrevious());
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * @param \Throwable $error
     * @return string
     */
    protected function renderXmlErrorMessage(\Throwable $error)
    {
        $xml = "<error>\n  <message>Application Error</message>\n";
        if ($this->debug) {
            do {
                $xml .= "  <exception>\n";
                $xml .= "    <type>" . get_class($error) . "</type>\n";
                $xml .= "    <code>" . $error->getCode() . "</code>\n";
                $xml .= "    <message>" . $this->createCdataSection($error->getMessage()) . "</message>\n";
                $xml .= "    <file>" . $error->getFile() . "</file>\n";
                $xml .= "    <line>" . $error->getLine() . "</line>\n";
                $xml .= "    <trace>" . $this->createCdataSection($error->getTraceAsString()) . "</trace>\n";
                $xml .= "  </exception>\n";
            } while ($error = $error->getPrevious());
        }
        $xml .= "</error>";

        return $xml;
    }

    /**
     * @param $content
     * @return string
     */
    private function createCdataSection($content)
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }


}
