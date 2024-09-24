protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth:api' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
    // Other middleware
];
