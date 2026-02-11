<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Core\Configure;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);
    
	$localesConfig = Configure::read('App.locales', ['fr' => 'fr_FR']);
    $defaultLang = Configure::read('App.defaultLanguage', 'fr');
    
    // Génère dynamiquement la chaîne "fr|en|es" pour la validation des routes
    $langs = implode('|', array_keys($localesConfig));

    // --- 1. ROUTES AVEC LANGUE DYNAMIQUE ---
    $routes->connect(
        '/{lang}/dashboard', 
        ['controller' => 'Posts', 'action' => 'dashboard'], 
        ['lang' => $langs]
    );

    $routes->connect(
        '/{lang}/posts', 
        ['controller' => 'Posts', 'action' => 'index'], 
        ['_name' => 'Posts_index', 'lang' => $langs]
    )->setExtensions(['json']);

    $routes->connect(
        '/{lang}/posts/{action}/*', 
        ['controller' => 'Posts'], 
        ['lang' => $langs]
    )->setExtensions(['json']);


    // --- 2. ROUTES SANS PRÉFIXE (Utilisant la config) ---
    $routes->connect(
        '/posts', 
        ['controller' => 'Posts', 'action' => 'index', 'lang' => $defaultLang]
    );

    $routes->connect(
        '/dashboard', 
        ['controller' => 'Posts', 'action' => 'dashboard', 'lang' => $defaultLang]
    );

    // Racine du site
    $routes->connect('/', [
        'controller' => 'Pages', 
        'action' => 'display', 
        'home', 
        'lang' => $defaultLang
    ]);
    
    $routes->connect('/pages/*', 'Pages::display');

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->fallbacks();
    });
};
