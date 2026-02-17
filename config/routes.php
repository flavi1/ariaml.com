<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Core\Configure;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);
    
    $localesConfig = Configure::read('App.locales', ['fr' => 'fr_FR']);
    $defaultLang = Configure::read('App.defaultLanguage', 'fr');
    $langs = implode('|', array_keys($localesConfig));
    $homeId = Configure::read('Settings.home_page_id');

    // --- 1. ROUTES D'ADMINISTRATION (Dashboard & CRUD) ---
    // On les place en premier pour qu'elles ne soient jamais confondues avec des slugs
    $routes->connect('/{lang}/dashboard', ['controller' => 'Posts', 'action' => 'dashboard'], ['lang' => $langs]);
    $routes->connect('/dashboard', ['controller' => 'Posts', 'action' => 'dashboard', 'lang' => $defaultLang]);

    $routes->connect('/{lang}/posts/{action}/*', ['controller' => 'Posts'], ['lang' => $langs])->setExtensions(['json']);
    $routes->connect('/posts/{action}/*', ['controller' => 'Posts', 'lang' => $defaultLang])->setExtensions(['json']);

	// --- 2. RACINES DU SITE (HOME) ---
	if ($homeId) {
		$routes->connect('/', 
			['controller' => 'Posts', 'action' => 'publicView', $homeId, 'lang' => $defaultLang],
			['_name' => 'home_default', 'pass' => [0]]
		);

		// On force cette route à ne matcher QUE le code langue exact (ex: /fr ou /en)
		$routes->connect('/{lang}', 
			['controller' => 'Posts', 'action' => 'publicView', $homeId],
			['lang' => $langs, '_name' => 'home_lang', 'pass' => [0]]
		);
	}

	// --- 3. ROUTES PUBLIQUES (SLUGS HIÉRARCHIQUES) ---
	// Notez le '+' après le deuxième slash : on veut AU MOINS un caractère après /fr/
	$routes->connect(
		'/{lang}/{path}', 
		['controller' => 'Posts', 'action' => 'publicView'],
		[
			'lang' => $langs, 
			'path' => '.+', // Regex : au moins un caractère
			'_name' => 'public_view_lang',
			'pass' => ['path']
		]
	);

	$routes->connect(
		'/{path}', 
		['controller' => 'Posts', 'action' => 'publicView', 'lang' => $defaultLang],
		[
			'path' => '.+', 
			'_name' => 'public_view',
			'pass' => ['path']
		]
	);

    // --- 4. FALLBACKS ---
    $routes->connect('/pages/*', 'Pages::display');
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->fallbacks();
    });
};
