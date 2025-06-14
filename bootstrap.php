<?php

use Slim\Views\Twig;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);

$app->add(TwigMiddleware::create($app, $twig));


$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// 404 Not Found
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails
) use ($app) {
    $view = Twig::fromRequest($request);
    $response = new \Slim\Psr7\Response();
    return $view->render($response->withStatus(404), 'errors/404.twig');
});

$errorMiddleware->setDefaultErrorHandler(function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails
) use ($twig): Response {
    $response = new \Slim\Psr7\Response();

    return $twig->render($response, 'errors/500.twig', [
        'message' => $exception->getMessage()
    ])->withStatus(500);
});

(require __DIR__ . '/routes/web.php')($app);

return $app;
