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
$router->get('/evenements', 'CalendarController@index'); // alias public pour les catégories
$router->get('/evenements/{id}', 'CalendarController@show');
$router->get('/api/events/{year}/{month}', 'CalendarController@apiEvents');

$router->get('/a-propos', 'AboutController@index');
$router->get('/sponsors', 'SponsorsController@index');

$router->get('/centre-media', 'MediaController@index');
$router->get('/centre-media/communiques/{slug}', 'MediaController@show');

$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@send');
$router->post('/contact/benevole', 'ContactController@submitVolunteer');
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
$router->get('/admin/analytics', 'Admin/AnalyticsController@index');

// Tableau de bord d'impact
$router->get('/admin/impact', 'Admin/ImpactController@index');
$router->post('/admin/impact/indicateurs', 'Admin/ImpactController@store');
$router->delete('/admin/impact/indicateurs/{id}', 'Admin/ImpactController@destroy');

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
// Catégories d'événements
$router->get('/admin/evenements/categories', 'Admin/EventCategoriesController@index');
$router->post('/admin/evenements/categories', 'Admin/EventCategoriesController@store');
$router->post('/admin/evenements/import-ics', 'Admin/EventsController@importIcs');
$router->get('/admin/evenements/categories/{id}/edit', 'Admin/EventCategoriesController@edit');
$router->put('/admin/evenements/categories/{id}', 'Admin/EventCategoriesController@update');
$router->delete('/admin/evenements/categories/{id}', 'Admin/EventCategoriesController@destroy');

// Actualités
$router->get('/admin/actualites', 'Admin/NewsAdminController@index');
$router->get('/admin/actualites/nouveau', 'Admin/NewsAdminController@create');
$router->get('/admin/actualites/{id}/edit', 'Admin/NewsAdminController@edit');
$router->post('/admin/actualites', 'Admin/NewsAdminController@store');
$router->post('/admin/actualites/generer-ia', 'Admin/NewsAdminController@generateDraft');
$router->put('/admin/actualites/{id}', 'Admin/NewsAdminController@update');
$router->delete('/admin/actualites/{id}', 'Admin/NewsAdminController@destroy');

// Demandes d'inscription
$router->get('/admin/inscriptions', 'Admin/InscriptionsController@index');
$router->post('/admin/inscriptions/{id}/valider', 'Admin/InscriptionsController@approve');
$router->post('/admin/inscriptions/{id}/refuser', 'Admin/InscriptionsController@reject');

// Bénévoles
$router->get('/admin/benevoles', 'Admin/VolunteersController@index');
$router->get('/admin/benevoles/{id}', 'Admin/VolunteersController@show');
$router->post('/admin/benevoles/{id}/status', 'Admin/VolunteersController@updateStatus');
$router->post('/admin/benevoles/{id}/delete', 'Admin/VolunteersController@destroy');

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

// Réseaux sociaux (assistant IA — brouillons validés manuellement)
$router->get('/admin/social', 'Admin/SocialMediaController@index');
$router->post('/admin/social/generer', 'Admin/SocialMediaController@generate');
$router->post('/admin/social/generer-matchs', 'Admin/SocialMediaController@generateMatches');
$router->put('/admin/social/{id}', 'Admin/SocialMediaController@update');
$router->post('/admin/social/{id}/approuver', 'Admin/SocialMediaController@approve');
$router->post('/admin/social/{id}/publier', 'Admin/SocialMediaController@publish');
$router->post('/admin/social/{id}/ignorer', 'Admin/SocialMediaController@ignore');
$router->get('/admin/social/comptes', 'Admin/SocialAccountsController@index');
$router->post('/admin/social/comptes/enregistrer', 'Admin/SocialAccountsController@save');
$router->post('/admin/social/comptes/competitions/{id}/toggle', 'Admin/SocialAccountsController@toggleCompetition');

// Partenaires
$router->get('/admin/partenaires', 'Admin/PartnersController@index');
$router->get('/admin/partenaires/nouveau', 'Admin/PartnersController@create');
$router->post('/admin/partenaires', 'Admin/PartnersController@store');
$router->get('/admin/partenaires/{id}/edit', 'Admin/PartnersController@edit');
$router->put('/admin/partenaires/{id}', 'Admin/PartnersController@update');
$router->delete('/admin/partenaires/{id}', 'Admin/PartnersController@destroy');

// Centre média
$router->get('/admin/media', 'Admin/MediaController@index');
$router->get('/admin/media/communiques/nouveau', 'Admin/MediaController@createRelease');
$router->post('/admin/media/communiques', 'Admin/MediaController@storeRelease');
$router->get('/admin/media/communiques/{id}/edit', 'Admin/MediaController@editRelease');
$router->put('/admin/media/communiques/{id}', 'Admin/MediaController@updateRelease');
$router->delete('/admin/media/communiques/{id}', 'Admin/MediaController@destroyRelease');
$router->post('/admin/media/kit', 'Admin/MediaController@storeKit');
$router->delete('/admin/media/kit/{id}', 'Admin/MediaController@destroyKit');
$router->post('/admin/media/revue', 'Admin/MediaController@storeCoverage');
$router->delete('/admin/media/revue/{id}', 'Admin/MediaController@destroyCoverage');
$router->post('/admin/media/contact', 'Admin/MediaController@saveContact');

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

// Paramètres
$router->get('/admin/settings', 'Admin/SettingsController@index');
$router->post('/admin/settings/profile', 'Admin/SettingsController@updateProfile');
$router->post('/admin/settings/password', 'Admin/SettingsController@updatePassword');
$router->post('/admin/settings/site', 'Admin/SettingsController@updateSite');