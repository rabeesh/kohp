<?php
use Aura\Router\RouterContainer;

class KohpRouter {
    private $routerContainer = null;

    function __construct() {
        $this->routerContainer = new RouterContainer();
    }

    function getMap() {
        return $this->routerContainer->getMap();
    }

    function use($ctx, $next) {
        $request = $ctx->req;
        $response = $ctx->res;
        $ctx->router = $this->routerContainer;

        /// get the route matcher from the container ...
        $matcher = $this->routerContainer->getMatcher();
        $route = $matcher->match($request);
        if (!$route) {
            echo "No route found for the request.";
            exit;
        }

        // add route attributes to the request
        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }
        $ctx->req = $request;

        // dispatch the request to the route handler.
        // (consider using https://github.com/auraphp/Aura.Dispatcher
        // in place of the one callable below.)
        $callable = $route->handler;
        $callable($ctx);

        echo $ctx->res->getBody();
    }
}
