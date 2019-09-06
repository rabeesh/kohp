<?php

// framework/index.php
require_once __DIR__.'/vendor/autoload.php';
require './Kohp.php';
require './KohpRouter.php';

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
