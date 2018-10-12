<?php

namespace InfamousQ\LManager\Middleware;

use InfamousQ\LManager\Kernel\MiddlewareAbstract;
use Slim\Http\Request;
use Slim\Http\Response;

class JWTAuthenticationMiddleware extends MiddlewareAbstract {

    public function __invoke(Request $request, Response $response, callable $next) {
        echo "JWTAuthenticationMiddleware - no JWT Token yet".PHP_EOL;
        $header_authorization = $request->getHeader('HTTP_AUTHORIZATION')[0];
        if (empty($header_authorization)) {
            // TODO: Can we get this URL dynamically?
            return $response->withRedirect('/user/token');
        }
        return $next($request, $response);
    }
}