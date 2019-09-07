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
$app = new Kohp();

// first handler function
$app->use(function ($ctx, $next) {
    $name = $ctx->req->getAttribute('name', 'Friend');
    $ctx->value  = 'Hello ' . $name;
    $next($ctx);
    echo "end of execute \n";
});

// use the router
$kohpRouter = new KohpRouter();
$map = $kohpRouter->getMap();
$map->get('blog.read', '/blog/{id}', function ($ctx) {
    $id = (int) $ctx->req->getAttribute('id');
    $ctx->res->getBody()->write("You asked for blog entry {$ctx->value} {$id}. \n" );
});

// kohp use aura router as router
// can configure any router
$app->use($kohpRouter);
$app->run();
```

