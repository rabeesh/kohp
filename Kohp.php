<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class Kohp {

    private $middlewares = [];
    public $ctx = null;

    public function __construct() {
        $this->ctx = $this->createContext();
    }

    public function use($handler) {
        $this->middlewares[] = $handler;
        return $this;
    }

    /**
     * Initialize a new context.
     *
     * @api private
     */
    function createContext() {
        // symfony request to match psr17
        $symfonyRequest = Request::createFromGlobals();

        // The HTTP_HOST server key must be set to avoid an unexpected error
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrRequest = $psrHttpFactory->createRequest($symfonyRequest);

        // create symfony response
        $symfonyResponse = new Response();
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrResponse = $psrHttpFactory->createResponse($symfonyResponse);

        $context = (object) [];
        $request = $psrRequest;
        $response = $psrResponse;
        $context->app = $request->app = $response->app = $this;
        $context->req = $request->req = $response->req = $request;
        $context->res = $request->res = $response->res = $response;
        $request->ctx = $response->ctx = $context;
        return $context;
    }

    public function run() {
        function execHandler($handler, $ctx, $wrappedNext = null) {
            if (is_callable($handler)) {
                $handler($ctx, $wrappedNext);
            } else {
                $handler->use($ctx, $wrappedNext);
            }
        };

        $wrappedMiddlewares = array_map(function($handler) {
            return function($next) use(&$handler) {
                $hasCalled = false;
                $wrappedNext = function ($ctx) use($next, &$hasCalled) {
                    if ($hasCalled) {
                        throw new Error('$next already called');
                    }
                    $hasCalled = true;
                    execHandler($next, $ctx);
                };

                return function($ctx) use($handler, &$wrappedNext, &$hasCalled) {
                    execHandler($handler, $ctx, $wrappedNext);
                    if (!$hasCalled) {
                        $wrappedNext($ctx);
                    }
                };
            };
        }, $this->middlewares);

        function build($middlewares, $handler) {
            for($i = count($middlewares) - 1; $i >= 0; $i--) {
                $handler = $middlewares[$i]($handler);
            }
            return $handler;
        }

        $voidFunc = function ($ctx, $next = null) {};
        $handler = build($wrappedMiddlewares, $voidFunc);
        $handler($this->ctx);
    }
}

