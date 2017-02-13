<?php

namespace app\helpers\handlers;

use Interop\Container\ContainerInterface as Container;
use Slim\Handlers\AbstractHandler;

/**
 * Error Handler
 * @package app\helpers\handlers
 */
class AbstractError extends AbstractHandler
{
    protected $container;

    protected $debug;

    protected $log;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->debug = $container->get('configs')['debug'];
        $this->log = $container->get('configs')['log'];
    }

    /**
     * @param $throwable
     * @param $name
     */
    protected function writeToErrorLog($throwable, $name)
    {
        if ($this->log) {
            $message = 'Application Error:' . PHP_EOL;
            $message .= $this->renderThrowableAsText($throwable);
            while ($throwable = $throwable->getPrevious()) {
                $message .= PHP_EOL . 'Previous error:' . PHP_EOL;
                $message .= $this->renderThrowableAsText($throwable);
            }
            $message .= PHP_EOL . '=================================';

            $this->container->get('logger')->$name($message);
        }
    }

    /**
     * @param \Exception|\Throwable $throwable
     * @return string
     */
    protected function renderThrowableAsText($throwable)
    {
        $text = sprintf('Type: %s' . PHP_EOL, get_class($throwable));

        if ($code = $throwable->getCode()) {
            $text .= sprintf('Code: %s' . PHP_EOL, $code);
        }

        if ($message = $throwable->getMessage()) {
            $text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
        }

        if ($file = $throwable->getFile()) {
            $text .= sprintf('File: %s' . PHP_EOL, $file);
        }

        if ($line = $throwable->getLine()) {
            $text .= sprintf('Line: %s' . PHP_EOL, $line);
        }

        if ($trace = $throwable->getTraceAsString()) {
            $text .= sprintf('Trace: %s', $trace);
        }

        return $text;
    }


}
