<?php 

protected $routeMiddleware = [
    // existing middlewares...
    'restrict.ip' => \App\Http\Middleware\RestrictIpAddresses::class,
];
