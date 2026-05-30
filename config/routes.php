<?php

/**
 * Définition de toutes les routes de l'application.
 *
 * @var \App\Core\Router $router
 */

// ---------------------------------------------------------------------------
// Routes publiques
// ---------------------------------------------------------------------------
$router->get('/', 'HomeController@index');

$router->get('/clubs', 'ClubsController@index');
$router->get('/clubs/{slug}', 'ClubsController@show');

$router->get('/actualites', 'NewsController@index');
$router->get('/actualites/{slug}', 'NewsController@show');

$router->get('/galerie', 'GalleryController@index');

$router->get('/calendrier', 'CalendarController@index');
$router->get('/api/events/{year}/{month}', 'CalendarController@apiEvents');

$router->get('/a-propos', 'AboutController@index');
$router->get('/sponsors', 'SponsorsController@index');

$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@send');
$router->post('/inscription', 'ContactController@register');

// ---------------------------------------------------------------------------
// Routes admin (préfixe /admin, protégées par le routeur)
// ---------------------------------------------------------------------------
$router->get('/admin/login', 'Admin/AuthController@loginForm');
$router->post('/admin/login', 'Admin/AuthController@login');
$router->post('/admin/logout', 'Admin/AuthController@logout');

$router->get('/admin', 'Admin/DashboardController@index');

// Membres
$router->get('/admin/membres', 'Admin/MembersController@index');
$router->get('/admin/membres/nouveau', 'Admin/MembersController@create');
$router->post('/admin/membres', 'Admin/MembersController@store');
$router->get('/admin/membres/{id}/edit', 'Admin/MembersController@edit');
$router->put('/admin/membres/{id}', 'Admin/MembersController@update');
$router->delete('/admin/membres/{id}', 'Admin/MembersController@destroy');

// Événements
$router->get('/admin/evenements', 'Admin/EventsController@index');
$router->get('/admin/evenements/nouveau', 'Admin/EventsController@create');
$router->post('/admin/evenements', 'Admin/EventsController@store');
$router->get('/admin/evenements/{id}/edit', 'Admin/EventsController@edit');
$router->put('/admin/evenements/{id}', 'Admin/EventsController@update');
$router->delete('/admin/evenements/{id}', 'Admin/EventsController@destroy');

// Actualités
$router->get('/admin/actualites', 'Admin/NewsAdminController@index');
$router->get('/admin/actualites/nouveau', 'Admin/NewsAdminController@create');
$router->post('/admin/actualites', 'Admin/NewsAdminController@store');
$router->put('/admin/actualites/{id}', 'Admin/NewsAdminController@update');
$router->delete('/admin/actualites/{id}', 'Admin/NewsAdminController@destroy');

// Documents
$router->get('/admin/documents', 'Admin/DocumentsController@index');
$router->post('/admin/documents', 'Admin/DocumentsController@upload');
$router->delete('/admin/documents/{id}', 'Admin/DocumentsController@destroy');

// Tâches
$router->get('/admin/taches', 'Admin/TasksController@index');
$router->post('/admin/taches', 'Admin/TasksController@store');
$router->put('/admin/taches/{id}', 'Admin/TasksController@update');
$router->delete('/admin/taches/{id}', 'Admin/TasksController@destroy');
