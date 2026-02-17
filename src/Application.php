<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since      3.3.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;


use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/* --- Imports pour l'Authentication --- */
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // By default, does not allow fallback classes.
        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
        
        // Chargement du plugin Authentication
        $this->addPlugin('Authentication');
        
		if (PHP_SAPI === 'cli') {
			return;
		}
        
		// Chargement dynamique des réglages depuis la table 'settings'
		try {
			$settingsTable = (new TableLocator())->get('Settings');
			$settings = $settingsTable->find()->all();
			
			foreach ($settings as $setting) {
				// On stocke sous le namespace 'Settings' pour éviter les conflits
				Configure::write('Settings.' . $setting->name, $setting->value);
			}
		} catch (\Exception $e) {
			// On échoue silencieusement si la table n'existe pas encore 
			// (utile lors de la première installation ou des migrations)
		}
        
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies
            ->add(new BodyParserMiddleware())

            // L'authentification doit se situer APRES le routing mais AVANT le CSRF
            ->add(new AuthenticationMiddleware($this))

            // Cross Site Request Forgery (CSRF) Protection Middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))
            
            // Multilangue (Middleware personnalisé)
			->add(function ($request, $handler) {
				$locales = \Cake\Core\Configure::read('App.locales', ['fr' => 'fr_FR']);
				$default = \Cake\Core\Configure::read('App.defaultLanguage', 'fr');
				
				// On récupère la langue de l'URL, sinon celle par défaut
				$lang = $request->getParam('lang', $default);
				
				// On applique la locale correspondante, ou la version FR par défaut si la clé n'existe pas
				$selectedLocale = $locales[$lang] ?? $locales[$default];
				
				\Cake\I18n\I18n::setLocale($selectedLocale);
				
				return $handler->handle($request);
			});

        return $middlewareQueue;
    }

    /**
     * Fournisseur de service d'authentification
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
	public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $authenticationService = new AuthenticationService([
            'unauthenticatedRedirect' => '/users/login',
            'queryParam' => 'redirect',
        ]);

        // 1. Session : pour persister la connexion
        $authenticationService->loadAuthenticator('Authentication.Session');

        // 2. Form : Configuré pour inclure directement son identificateur (évite le deprecated)
        $authenticationService->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'username',
                'password' => 'password',
            ],
            'loginUrl' => '/users/login',
            // On injecte l'identificateur ici directement
            'identifiers' => [
                'Authentication.Password' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password',
                    ]
                ]
            ]
        ]);

        return $authenticationService;
    }

    /**
     * Register application container services.
     */
    public function services(ContainerInterface $container): void
    {
        // ...
    }

    /**
     * Register custom event listeners here
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        return $eventManager;
    }
}
