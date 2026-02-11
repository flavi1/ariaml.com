<?php
declare(strict_types=1);
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;


/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
	
	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
		// Autoriser login, logout et votre méthode seed (token)
		$this->Authentication->addUnauthenticatedActions(['login', 'logout', 'seed']);
	}
	
	
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: []);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // Si l'utilisateur est déjà connecté, on le redirige
        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Posts',
                'action' => 'index',
            ]);

            return $this->redirect($redirect);
        }

        // Si l'utilisateur a soumis le formulaire et que ça a échoué
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Identifiant ou mot de passe invalide'));
        }
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }
    


	/**
	 * Création du premier utilisateur via token de sécurité
	 * URL : /users/seed?token=VOTRE_MIGRATION_TOKEN
	 */
	public function seed()
	{
		$token = $this->request->getQuery('token');
		$expectedToken = Configure::read('Security.migration_token');
		$firstPass = Configure::read('Security.firstPassword');

		// Sécurité : on compare le token
		if (!$token || $token !== $expectedToken) {
			throw new ForbiddenException('Token de migration invalide.');
		}

		// On vérifie si un admin existe déjà pour éviter les doublons
		if ($this->Users->find()->count() > 0) {
			$this->Flash->error('Un utilisateur existe déjà.');
			return $this->redirect(['action' => 'login']);
		}

		$user = $this->Users->newEmptyEntity();
		$user->username = 'admin'; // Vous pourrez le changer plus tard
		$user->password = $firstPass; // À changer après connexion !

		if ($this->Users->save($user)) {
			$this->Flash->success('Utilisateur admin créé avec succès.');
		} else {
			$this->Flash->error('Erreur lors de la création.');
		}

		return $this->redirect(['action' => 'login']);
	}    
}
