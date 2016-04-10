<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
  'security.firewalls' => array(
    'secured' => array(
      'pattern' => '^/',
      'anonymous' => true,
      'logout' => true,
      'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
      'users' => $app->share(function () use ($app) {
          return new WebLinks\DAO\UserDAO($app['db']);
      }),
    ),
  ),
));

// Register services
$app['dao.link'] = $app->share(function ($app) {
    $linkDAO = new WebLinks\DAO\LinkDAO($app['db']);
    $linkDAO->setUserDAO($app['dao.user']);
    return $linkDAO;
});

$app['dao.user'] = $app->share(function ($app) {
    $userDAO = new WebLinks\DAO\UserDAO($app['db']);
    return $userDAO;
});
