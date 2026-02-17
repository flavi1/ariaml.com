<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Core\Configure;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // 1. Initialisation des variables
    $locales = Configure::read('App.locales', ['fr' => 'fr_FR']);
    $langs = implode('|', array_keys($locales));
    $defaultLang = Configure::read('App.defaultLanguage', 'fr');
    $homeId = Configure::read('Settings.home_page_id');

    // --- 2. ROUTES SYSTÈME ET DEVOPS ---
    $routes->connect('/dev-ops/migrate', ['controller' => 'DevOps', 'action' => 'migrate']);
    
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login'], ['_name' => 'login']);
    $routes->connect('/users/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);

    // --- 3. ROUTES D'ADMINISTRATION ---
    $routes->scope('/', function (RouteBuilder $builder) use ($langs, $defaultLang) {
        $builder->connect('/{lang}/dashboard', ['controller' => 'Posts', 'action' => 'dashboard'], ['lang' => $langs]);
        $builder->connect('/dashboard', ['controller' => 'Posts', 'action' => 'dashboard', 'lang' => $defaultLang]);

        $builder->connect('/{lang}/posts/{action}/*', ['controller' => 'Posts'], ['lang' => $langs]);
        $builder->connect('/posts/{action}/*', ['controller' => 'Posts', 'lang' => $defaultLang]);
    });

	// --- 4. RACINES DU SITE (HOME) ---
	// site.com/
	$routes->connect('/', 
		['controller' => 'Posts', 'action' => 'publicView', 'isHome' => true, 'lang' => $defaultLang], 
		['_name' => 'home_default']
	);

	// site.com/fr
	$routes->connect('/{lang}', 
		['controller' => 'Posts', 'action' => 'publicView', 'isHome' => true], 
		['lang' => $langs, '_name' => 'home_lang']
	);

    // --- 5. ROUTES PUBLIQUES (SLUGS HIÉRARCHIQUES) ---
    // On utilise une regex qui exclut les routes déjà définies si nécessaire
    
    // site.com/fr/parent/enfant
    $routes->connect('/{lang}/{path}', 
        ['controller' => 'Posts', 'action' => 'publicView'],
        [
            'lang' => $langs,
            'path' => '[a-zA-Z0-9\/\-]+', 
            '_name' => 'slug_lang',
            'pass' => ['path']
        ]
    );

    // site.com/parent/enfant
    $routes->connect('/{path}', 
        ['controller' => 'Posts', 'action' => 'publicView', 'lang' => $defaultLang],
        [
            'path' => '[a-zA-Z0-9\/\-]+',
            '_name' => 'slug_default',
            'pass' => ['path']
        ]
    );

    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks();
    });
};
