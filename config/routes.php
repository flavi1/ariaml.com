<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

	$routes->connect('/{lang}/dashboard', ['controller' => 'Articles', 'action' => 'dashboard'], ['lang' => 'fr|en']);

    // 1. LES ROUTES PRIORITAIRES (SANS SCOPE)
    // On définit explicitement les articles avec la langue AVANT tout le reste
    $routes->connect(
        '/{lang}/articles', 
        ['controller' => 'Articles', 'action' => 'index'], 
        ['_name' => 'articles_index', 'lang' => 'fr|en']
    )->setExtensions(['json']);

    $routes->connect(
        '/{lang}/articles/{action}/*', 
        ['controller' => 'Articles'], 
        ['lang' => 'fr|en']
    )->setExtensions(['json']);

    // 2. LA RACINE DU SITE
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
    
    // 3. LE RESTE DES PAGES STATIQUES
    $routes->connect('/pages/*', 'Pages::display');

    // 4. LE FALLBACK GÉNÉRAL (POUR LE RESTE)
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->fallbacks();
    });
};
