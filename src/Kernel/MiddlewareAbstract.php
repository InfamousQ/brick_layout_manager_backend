<?php

namespace InfamousQ\LManager\Kernel;

use \Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use InfamousQ\LManager\App;

abstract class MiddlewareAbstract {

    /** @var ContainerInterface Slim container */
    protected $container;

    public function __construct (ContainerInterface $container) {
        $this->container = $container;
        unset($container);
    }

    /**
     * Middleware functionality
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    abstract public function __invoke(Request $request, Response $response, callable $next);

    /**
     * get Slim container
     * @return ContainerInterface
     */
    protected function getContainer() {
        return $this->container;
    }


    /**
     * Get Service from Container
     * @param string $service
     * @return mixed
     */
    protected function getService ($service) {
        return $this->container->{$service};
    }
}