<?php

use Symfony\Component\HttpFoundation\Request;
use WebLinks\Domain\Link;
use WebLinks\Domain\User;
use WebLinks\Form\Type\LinkType;
use WebLinks\Form\Type\UserType;

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

// Add a new link
$app->match('admin/link/add', function(Request $request) use ($app) {
  $link = new Link();
  $linkForm = $app['form.factory']->create(new LinkType(), $link);
  $linkForm->handleRequest($request);
  if ($linkForm->isSubmitted() && $linkForm->isValid())
  {
    $app['dao.link']->save($link);
    $app['session']->getFlashBag()->add('success', 'The link was successully created');
  }
  return $app['twig']->render('link_form.html.twig', array(
    'title' => 'New link',
    'linkForm' => $linkForm->createView()
  ));
})->bind('admin_link_add');

// Edit an existing link
$app->match('/admin/link/{id}/edit', function($id, Request $request) use ($app) {
  $link = $app['dao.link']->find($id);
  $linkForm = $app['form.factory']->create(new LinkType(), $link);
  $linkForm->handleRequest($request);
  if ($linkForm->isSubmitted() && $linkForm->isValid()) {
    $app['dao.link']->save($link);
    $app['session']->getFlashBag()->add('success', 'The link was succesfully updated.');
  }
  return $app['twig']->render('link_form.html.twig', array(
    'title' => 'Edit link',
    'linkForm' => $linkForm->createView()));
})->bind('admin_link_edit');

// Remove an link
$app->get('/admin/link/{id}/delete', function($id, Request $request) use ($app) {
  // Delete the link
  $app['dao.link']->delete($id);
  $app['session']->getFlashBag()->add('success', 'The link was succesfully removed.');
  // Redirect to admin home page
  return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_link_delete');


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

// Add a user
$app->match('/admin/user/add', function(Request $request) use ($app) {
  $user = new User();
  $userForm = $app['form.factory']->create(new UserType(), $user);
  $userForm->handleRequest($request);
  if ($userForm->isSubmitted() && $userForm->isValid()) {
    // generate a random salt value
    $salt = substr(md5(time()), 0, 23);
    $user->setSalt($salt);
    $plainPassword = $user->getPassword();
    // find the default encoder
    $encoder = $app['security.encoder.digest'];
    // compute the encoded password
    $password = $encoder->encodePassword($plainPassword, $user->getSalt());
    $user->setPassword($password);
    $app['dao.user']->save($user);
    $app['session']->getFlashBag()->add('success', 'The user was successfully created.');
  }
  return $app['twig']->render('user_form.html.twig', array(
    'title' => 'New user',
    'userForm' => $userForm->createView()));
})->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
  $user = $app['dao.user']->find($id);
  $userForm = $app['form.factory']->create(new UserType(), $user);
  $userForm->handleRequest($request);
  if ($userForm->isSubmitted() && $userForm->isValid()) {
    $plainPassword = $user->getPassword();
    // find the encoder for the user
    $encoder = $app['security.encoder_factory']->getEncoder($user);
    // compute the encoded password
    $password = $encoder->encodePassword($plainPassword, $user->getSalt());
    $user->setPassword($password);
    $app['dao.user']->save($user);
    $app['session']->getFlashBag()->add('success', 'The user was succesfully updated.');
  }
  return $app['twig']->render('user_form.html.twig', array(
    'title' => 'Edit user',
    'userForm' => $userForm->createView()));
})->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', function($id, Request $request) use ($app) {
  // Delete all associated comments
  $app['dao.link']->deleteAllByUser($id);
  // Delete the user
  $app['dao.user']->delete($id);
  $app['session']->getFlashBag()->add('success', 'The user was succesfully removed.');
  // Redirect to admin home page
  return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_user_delete');

// API : get all articles
$app->get('/api/links', function() use ($app) {
  $links = $app['dao.link']->findAll();
  // Convert an array of objects ($links) into an array of associative arrays ($responseData)
  $responseData = array();
  foreach ($links as $link) {
    $responseData[] = array(
      'id' => $link->getId(),
      'title' => $link->getTitle(),
      'url' => $link->getUrl()
    );
  }
  // Create and return a JSON response
  return $app->json($responseData);
})->bind('api_links');

// API : get an article
$app->get('/api/link/{id}', function($id) use ($app) {
  $link = $app['dao.link']->find($id);
  // Convert an object ($link) into an associative array ($responseData)
  $responseData = array(
    'id' => $link->getId(),
    'title' => $link->getTitle(),
    'url' => $link->getUrl()
  );
  // Create and return a JSON response
  return $app->json($responseData);
})->bind('api_link');