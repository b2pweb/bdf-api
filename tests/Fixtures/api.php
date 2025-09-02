<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$app = new \Bdf\Fixtures\TestApplication();
$response = $app->handle($request = \Symfony\Component\HttpFoundation\Request::createFromGlobals());

$response->send();
$app->terminate($request, $response);
