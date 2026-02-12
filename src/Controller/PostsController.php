<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Posts Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // On utilise l'ordre du TreeBehavior (lft) pour garder la structure logique du document
        $query = $this->Posts->find()
            ->contain(['Translations'])
            ->orderBy(['Posts.lft' => 'ASC']);

        $posts = $this->paginate($query);
        $this->set(compact('posts'));

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
        $post = $this->Posts->get($id, [
            'contain' => ['Translations', 'ParentPosts', 'ChildPosts'],
        ]);
        $this->set(compact('post'));
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
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The node could not be saved. Please, try again.'));
        }

        $parentPosts = $this->Posts->ParentPosts->find('treeList');

        $locales = Configure::read('App.locales');
        $defaultLang = Configure::read('App.defaultLanguage');
        $secondaryLangs = array_keys(array_diff_key($locales, [$defaultLang => '']));

        $this->set(compact('post', 'parentPosts', 'defaultLang', 'secondaryLangs'));
    }

    /**
     * Edit method
     */
    public function edit($id = null)
    {
        $post = $this->Posts->get($id, [
            'contain' => ['Translations']
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $post = $this->Posts->patchEntity($post, $this->request->getData(), [
                'translations' => true
            ]);

            if ($this->Posts->save($post)) {
                $this->Flash->success(__('The node has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The node could not be saved. Please, try again.'));
        }

        $parentPosts = $this->Posts->ParentPosts->find('treeList');

        $locales = Configure::read('App.locales');
        $defaultLang = Configure::read('App.defaultLanguage');
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
        
        // Le TreeBehavior gère automatiquement les enfants lors de la suppression
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The node has been deleted.'));
        } else {
            $this->Flash->error(__('The node could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Dashboard method (AriaML / AngularJS)
     */
    public function dashboard()
    {
        // Ici, on peut injecter la config nécessaire au JS
        $config = [
            'locales' => Configure::read('App.locales'),
            'default' => Configure::read('App.defaultLanguage')
        ];
        $this->set(compact('config'));
    }
}
