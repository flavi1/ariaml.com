<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Core\Configure;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // Initialisation des variables globales AriaML
    $locales = Configure::read('App.locales', ['fr' => 'fr_FR']);
    $langs = implode('|', array_keys($locales));
    $defaultLang = Configure::read('App.defaultLanguage', 'fr');
    $homeId = Configure::read('Settings.home_page_id');

    // --- A. ROUTES SYSTÈME (Priorité Maximale) ---
    // On définit explicitement les routes qui ne doivent JAMAIS être des slugs
	$routes->connect('/dev-ops/migrate', ['controller' => 'DevOps', 'action' => 'migrate']);
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);

    // --- B. ROUTES DASHBOARD / ADMIN ---
    $routes->scope('/', function (RouteBuilder $builder) use ($langs, $defaultLang) {
        // Dashboard avec ou sans langue
        $builder->connect('/{lang}/dashboard', ['controller' => 'Posts', 'action' => 'dashboard'], ['lang' => $langs]);
        $builder->connect('/dashboard', ['controller' => 'Posts', 'action' => 'dashboard', 'lang' => $defaultLang]);

        // CRUD Posts (Edit, Add, Delete, Index)
        $builder->connect('/{lang}/posts/{action}/*', ['controller' => 'Posts'], ['lang' => $langs]);
        $builder->connect('/posts/{action}/*', ['controller' => 'Posts', 'lang' => $defaultLang]);
    });

    // --- C. RACINES DU SITE (HOME) ---
    if ($homeId) {
        // Racine pure (site.com/)
        $routes->connect('/', 
            ['controller' => 'Posts', 'action' => 'publicView', $homeId, 'lang' => $defaultLang],
            ['_name' => 'home_default', 'pass' => [0]]
        );

        // Racine par langue (site.com/fr)
        // La regex '$' assure que ça ne match pas /fr/quelque-chose
        $routes->connect('/{lang}', 
            ['controller' => 'Posts', 'action' => 'publicView', $homeId],
            ['lang' => $langs, '_name' => 'home_lang', 'pass' => [0]]
        );
    }

    // --- D. ROUTES PUBLIQUES (SLUGS) ---
    // Ces routes capturent tout ce qui n'a pas été matché au-dessus
    
    // site.com/fr/parent/enfant
    $routes->connect('/{lang}/{path}', 
        ['controller' => 'Posts', 'action' => 'publicView'],
        [
            'lang' => $langs,
            'path' => '[a-zA-Z0-9\/\-]+', // Regex autorisant les slugs et les slashs
            '_name' => 'slug_lang',
            'pass' => ['path']
        ]
    );

    // site.com/parent/enfant (langue par défaut)
    $routes->connect('/{path}', 
        ['controller' => 'Posts', 'action' => 'publicView', 'lang' => $defaultLang],
        [
            'path' => '[a-zA-Z0-9\/\-]+',
            '_name' => 'slug_default',
            'pass' => ['path']
        ]
    );

    // Fallbacks finaux
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks();
    });
};
