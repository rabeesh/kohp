# Kohp

Kohp is experiment attempt similar to other middleware framework like koa.js

# Run example

To run this project

```
$ composer install
$ php -S localhost:8000
```

Visit browser `http://localhost:8000/blog/133`

# Usage

Example usage of middleware and router

```

use Aura\Router\RouterContainer;

// framework/index.php
require_once __DIR__.'/vendor/autoload.php';
require './Kohp.php';

$app = new Kohp();

// first handler function
$app->use(function ($ctx, $next) {
    $name = $ctx->req->getAttribute('name', 'Friend');
    $ctx->value  = 'Hello ' . $name;
    $next($ctx);
    echo "end of execute \n";
});

// Kohp uses aura router as router
// Can configure any route engine
$routerContainer = new RouterContainer();
$app->ctx->router = $routerContainer;
$map = $routerContainer->getMap();

$map->get('blog.read', '/blog/{id}', function ($ctx) {
    $id = (int) $ctx->req->getAttribute('id');
    $ctx->res->getBody()->write("You asked for blog entry of {$ctx->value} {$id}. \n" );
});

$app->use(function ($ctx, $next) {
    $request = $ctx->req;
    $response = $ctx->res;

    /// get the route matcher from the container ...
    $matcher = $ctx->router->getMatcher();
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
});

$app->run();

```

