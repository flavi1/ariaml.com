<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;

/**
 * Posts Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * dashboard method
     */

	public function dashboard()
	{
		
/*
        $config = [
            'locales' => Configure::read('App.locales') ?? [],
            'default' => Configure::read('App.defaultLanguage', 'fr')
        ];
        $this->set(compact('config'));

*/
		
		// En CakePHP 5, on utilise fetchTable()
		$settingsTable = $this->fetchTable('Settings');

		if ($this->request->is(['post', 'put'])) {
			$homeId = $this->request->getData('home_page_id');
			if ($homeId) {
				$setting = $settingsTable->findOrCreate(['name' => 'home_page_id']);
				$setting->value = (string)$homeId; 
				$settingsTable->save($setting);
				
				$this->Flash->success(__('Home page updated.'));
				return $this->redirect(['action' => 'dashboard']);
			}
		}

		// On lit la config chargée via le bootstrap de l'Application
		$currentPageId = \Cake\Core\Configure::read('Settings.home_page_id');

		$query = $this->Posts->find('translations') 
			->orderBy(['Posts.lft' => 'ASC']);

		$posts = $this->paginate($query);
		
		$this->set(compact('posts', 'currentPageId'));

		if ($this->request->is('json')) {
			$this->viewBuilder()->setClassName('Json');
			$this->viewBuilder()->setOption('serialize', ['posts']);
		}
	}

    /**
     * View method
     */
    public function view($id = null)
    {
        // Correction : Arguments nommés pour CakePHP 5
        $post = $this->Posts->get($id, contain: ['PostsTranslations', 'ParentPosts', 'ChildPosts']);
        $this->set(compact('post'));
    }
    
	public function publicView($idOrPath = null)
	{
		$lang = $this->request->getParam('lang');
		\Cake\I18n\I18n::setLocale($lang);

		// Récupération de l'ID de la home (chargé dans Application.php)
		$homeId = \Cake\Core\Configure::read('Settings.home_page_id');
		$isHomeRequested = $this->request->getParam('isHome', false);

		// 1. Détermination de la cible (ID ou Slug)
		if ($isHomeRequested) {
			if (!$homeId) {
				throw new \Cake\Datasource\Exception\RecordNotFoundException("Configuration 'home_page_id' manquante.");
			}
			$query = $this->Posts->find('translations', locale: $lang)
				->where(['Posts.id' => (int)$homeId]);
		} elseif (is_numeric($idOrPath)) {
			$query = $this->Posts->find('translations', locale: $lang)
				->where(['Posts.id' => (int)$idOrPath]);
		} else {
			$slug = end(explode('/', (string)$idOrPath));
			$query = $this->Posts->find('translations', locale: $lang)
				->where([
					'OR' => [
						'Posts.slug' => $slug,
						'PostsTranslation.slug' => $slug
					]
				]);
		}

		$post = $query->contain(['ParentPosts', 'ChildPosts'])->firstOrFail();

		// 2. Rigueur SEO (Uniquement pour les accès par slug)
		if (!$isHomeRequested && !is_numeric($idOrPath)) {
			$isDefault = ($lang === \Cake\Core\Configure::read('App.defaultLanguage'));
			$translatedSlug = $post->_translations[$lang]->slug ?? null;
			$expectedSlug = ($isDefault || empty($translatedSlug)) ? $post->slug : $translatedSlug;

			if ($slug !== $expectedSlug) {
				return $this->redirect(['lang' => $lang, 'path' => $expectedSlug], 301);
			}
		}

		$isHome = (int)$post->id === (int)$homeId;
		$this->set(compact('post', 'isHome'));
	}
	
    /**
     * Add method
     */
    public function add()
    {
        $post = $this->Posts->newEmptyEntity();
        if ($this->request->is('post')) {
            $post = $this->Posts->patchEntity($post, $this->request->getData(), [
                'translations' => true
            ]);
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('The node has been saved.'));
                return $this->redirect(['action' => 'dashboard']);
            }
            $this->Flash->error(__('The node could not be saved. Please, try again.'));
        }

        // Correction : Suppression de la dépréciation treeList
        $parentPosts = $this->Posts->ParentPosts->find('treeList', 
            keyPath: 'id',
            valuePath: 'title',
            spacer: '→ '
        )->toArray();

        // Correction : Lecture sécurisée de la config
        $locales = Configure::read('App.locales') ?? [];
        $defaultLang = Configure::read('App.defaultLanguage', 'fr');
        $secondaryLangs = array_keys(array_diff_key($locales, [$defaultLang => '']));

        $this->set(compact('post', 'parentPosts', 'defaultLang', 'secondaryLangs'));
    }

    /**
     * Edit method
     */
    public function edit($id = null)
    {
        // Correction : Arguments nommés pour CakePHP 5
		$post = $this->Posts->find('translations')
			->where(['Posts.id' => $id])
			->contain(['ParentPosts']) // Charge les autres associations normalement
			->firstOrFail();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$post = $this->Posts->patchEntity($post, $this->request->getData(), [
				'translations' => true
			]);

			if ($this->Posts->save($post)) {
				$this->Flash->success(__('The node has been saved.'));
				return $this->redirect(['action' => 'dashboard']);
			}
			$this->Flash->error(__('The node could not be saved. Please, try again.'));
		}

        // Correction : Suppression de la dépréciation treeList
        $parentPosts = $this->Posts->ParentPosts->find('treeList', 
            keyPath: 'id',
            valuePath: 'title',
            spacer: '→ '
        )->toArray();

        // Correction : Lecture sécurisée de la config
        $locales = Configure::read('App.locales') ?? [];
        $defaultLang = Configure::read('App.defaultLanguage', 'fr');
        $secondaryLangs = array_keys(array_diff_key($locales, [$defaultLang => '']));

        $this->set(compact('post', 'parentPosts', 'defaultLang', 'secondaryLangs'));
    }

    /**
     * Delete method
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $post = $this->Posts->get($id);
        
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The node has been deleted.'));
        } else {
            $this->Flash->error(__('The node could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'dashboard']);
    }

}
