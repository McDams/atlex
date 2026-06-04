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

$router->get('/athletes', 'AthletesController@index');
$router->get('/athletes/{slug}', 'AthletesController@show');

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

$router->get('/confidentialite', 'PrivacyController@index');
$router->get('/sitemap.xml', 'SitemapController@index');

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

// Athlètes
$router->get('/admin/athletes', 'Admin/AthletesController@index');
$router->get('/admin/athletes/nouveau', 'Admin/AthletesController@create');
$router->post('/admin/athletes', 'Admin/AthletesController@store');
$router->get('/admin/athletes/{id}/edit', 'Admin/AthletesController@edit');
$router->put('/admin/athletes/{id}', 'Admin/AthletesController@update');
$router->delete('/admin/athletes/{id}', 'Admin/AthletesController@destroy');

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

// Demandes d'inscription
$router->get('/admin/inscriptions', 'Admin/InscriptionsController@index');
$router->post('/admin/inscriptions/{id}/valider', 'Admin/InscriptionsController@approve');
$router->post('/admin/inscriptions/{id}/refuser', 'Admin/InscriptionsController@reject');

// Projets
$router->get('/admin/projets', 'Admin/ProjectsController@index');
$router->get('/admin/projets/nouveau', 'Admin/ProjectsController@create');
$router->post('/admin/projets', 'Admin/ProjectsController@store');
$router->get('/admin/projets/{id}/edit', 'Admin/ProjectsController@edit');
$router->put('/admin/projets/{id}', 'Admin/ProjectsController@update');
$router->delete('/admin/projets/{id}', 'Admin/ProjectsController@destroy');

// Recherche de financements
$router->get('/admin/financements', 'Admin/FundingController@index');
$router->get('/admin/financements/nouveau', 'Admin/FundingController@create');
$router->post('/admin/financements', 'Admin/FundingController@store');
$router->get('/admin/financements/{id}/edit', 'Admin/FundingController@edit');
$router->put('/admin/financements/{id}', 'Admin/FundingController@update');
$router->delete('/admin/financements/{id}', 'Admin/FundingController@destroy');

// Démarches à suivre (checklist d'une opportunité)
$router->post('/admin/financements/{id}/checklist', 'Admin/FundingController@addChecklist');
$router->post('/admin/financements/{id}/checklist/seed', 'Admin/FundingController@seedChecklist');
$router->put('/admin/financements/checklist/{item}', 'Admin/FundingController@toggleChecklist');
$router->delete('/admin/financements/checklist/{item}', 'Admin/FundingController@deleteChecklist');

// Veille de financements (opportunités détectées automatiquement)
$router->get('/admin/veille', 'Admin/FundingWatchController@index');
$router->post('/admin/veille/refresh', 'Admin/FundingWatchController@refresh');
$router->get('/admin/veille/sources', 'Admin/FundingWatchController@sources');
$router->post('/admin/veille/sources', 'Admin/FundingWatchController@storeSource');
$router->put('/admin/veille/sources/{id}', 'Admin/FundingWatchController@updateSource');
$router->delete('/admin/veille/sources/{id}', 'Admin/FundingWatchController@destroySource');
$router->post('/admin/veille/template', 'Admin/FundingWatchController@saveTemplate');
$router->post('/admin/veille/{id}/promouvoir', 'Admin/FundingWatchController@promote');
$router->post('/admin/veille/{id}/ignorer', 'Admin/FundingWatchController@ignore');

// Partenaires
$router->get('/admin/partenaires', 'Admin/PartnersController@index');
$router->get('/admin/partenaires/nouveau', 'Admin/PartnersController@create');
$router->post('/admin/partenaires', 'Admin/PartnersController@store');
$router->get('/admin/partenaires/{id}/edit', 'Admin/PartnersController@edit');
$router->put('/admin/partenaires/{id}', 'Admin/PartnersController@update');
$router->delete('/admin/partenaires/{id}', 'Admin/PartnersController@destroy');

// Documents
$router->get('/admin/documents', 'Admin/DocumentsController@index');
$router->post('/admin/documents', 'Admin/DocumentsController@upload');
$router->delete('/admin/documents/{id}', 'Admin/DocumentsController@destroy');

// Tâches
$router->get('/admin/taches', 'Admin/TasksController@index');
$router->post('/admin/taches', 'Admin/TasksController@store');
$router->put('/admin/taches/{id}', 'Admin/TasksController@update');
$router->delete('/admin/taches/{id}', 'Admin/TasksController@destroy');

// Hostinger monitoring
$router->get('/admin/hostinger', 'Admin/HostingerController@index');
$router->post('/admin/hostinger/save', 'Admin/HostingerController@save');
$router->post('/admin/hostinger/test', 'Admin/HostingerController@test');
