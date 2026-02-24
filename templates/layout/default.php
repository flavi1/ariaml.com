<?php
/**
 * @var \App\View\AppView $this
 * @var string $cakeDescription
 */
use Cake\Core\Configure;

$cakeDescription = 'CakePHP: the rapid development php framework';

// Préparation des langues
$locales = Configure::read('App.locales', ['fr' => 'fr_FR']);
$defaultLang = Configure::read('App.defaultLanguage', 'fr');
$currentLang = $this->request->getParam('lang', $defaultLang);

// On récupère les paramètres de la route actuelle (controller, action, slugs...)
// pour que le changement de langue reste sur la même page
$queryParams = $this->request->getAttribute('params');
unset($queryParams['pass'], $queryParams['_matchedRoute'], $queryParams['_Token'], $queryParams['isAjax']);
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    
    <style>
        .lang-switcher { display: inline-block; margin-left: 2rem; font-size: 1.2rem; }
        .lang-item { font-weight: bold; text-decoration: none; padding: 0 0.5rem; }
        .lang-item.active { color: #d33c43; text-decoration: underline; }
        .lang-separator { color: #606c76; opacity: 0.5; }
    </style>
</head>
<body>
	<div class="logo"><img alt="Aria Markup Language" src="https://flavi1.github.io/aria-ml/src/icon/512.png"></div>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Aria</span>ML</a>
            
			<div class="lang-switcher">
				<?php 
				// Préparation des paramètres pour les pages de contenu (hors home)
				$isHome = $this->get('isHome', false);

				foreach ($locales as $code => $locale): 
					// Style CSS
					$class = 'lang-item' . ($currentLang === $code ? ' active' : '');
					$label = strtoupper($code);
					
					// Séparateur (sauf pour le premier élément)
					if ($code !== array_key_first($locales)) {
						echo '<span class="lang-separator">|</span>';
					}

					if ($isHome) {
						// SI HOME : On force l'utilisation des routes nommées dédiées
						$routeName = ($code === $defaultLang) ? 'home_default' : 'home_lang';
						echo $this->Html->link($label, ['_name' => $routeName, 'lang' => $code], ['class' => $class]);
					} else {
						// SI PAGE CLASSIQUE : On garde la logique de fusion des paramètres (Slugs, etc.)
						echo $this->Html->link($label, array_merge($queryParams, ['lang' => $code]), ['class' => $class]);
					}
				endforeach; 
				?>
			</div>
        </div>
        <div class="top-nav-links">
            <!--a target="_blank" rel="noopener" href="https://book.cakephp.org/5/">Documentation</a>
            <a target="_blank" rel="noopener" href="https://api.cakephp.org/">API</a-->
            <span>Github:</span>
            <a target="_blank" rel="noopener" href="https://github.com/flavi1/aria-ml">Webextension &amp; JS Polyfill</a>
            <a target="_blank" rel="noopener" href="https://github.com/flavi1/ariaml-ssr-php">SSR PHP Helper</a>
            <a target="_blank" rel="noopener" href="https://github.com/flavi1/ariaml.com">This Website</a>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
