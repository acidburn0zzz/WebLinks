<?php

use Symfony\Component\HttpFoundation\Request;
use WebLinks\Domain\Link;
use WebLinks\Domain\User;
use WebLinks\Form\Type\LinkType;

// Home page
$app->get('/', function () use ($app) {
  $links = $app['dao.link']->findAll();
  return $app['twig']->render('index.html.twig', array('links' => $links));
});

// Link submit
$app->match('/link/submit', function (Request $request) use ($app) {
  $link = new Link();
  $user = $app['security']->getToken()->getUser();
  if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
    $link->setUser($user);
    $linkForm = $app['form.factory']->create(new LinkType(), $link);
    $linkForm->handleRequest($request);
    if ($linkForm->isValid()) {
      $app['dao.link']->save($link);
      $app['session']->getFlashBag()->add('success', 'Your link was succesfully added.');
    }
    $linkFormView = $linkForm->createView();
  }
  return $app['twig']->render('link.html.twig', array('linkForm' => $linkFormView));
});


// Login form
$app->get('/login', function(Request $request) use ($app) {
  return $app['twig']->render('login.html.twig', array(
    'error'         => $app['security.last_error']($request),
    'last_username' => $app['session']->get('_security.last_username'),
  ));
})->bind('login');

// Admin home page
$app->get('/admin', function () use ($app) {
  $links = $app['dao.link']->findAll();
  $users = $app['dao.user']->findAll();
  return $app['twig']->render('admin.html.twig', array(
    'links' => $links,
    'users' => $users
  ));
})->bind('admin');