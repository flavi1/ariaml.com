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
     * Index method
     */
    public function index()
    {
        // Utilisation de l'ordre structurel du TreeBehavior
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
        // Correction : Arguments nommés pour CakePHP 5
        $post = $this->Posts->get($id, contain: ['Translations', 'ParentPosts', 'ChildPosts']);
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
        $post = $this->Posts->get($id, contain: ['Translations']);

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

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Dashboard method (CmsDashboard)
     */
    public function dashboard()
    {
        $config = [
            'locales' => Configure::read('App.locales') ?? [],
            'default' => Configure::read('App.defaultLanguage', 'fr')
        ];
        $this->set(compact('config'));
    }
}
