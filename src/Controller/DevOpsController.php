<?php
namespace App\Controller;

use Cake\Core\Configure;
use Migrations\Migrations;

class DevOpsController extends AppController
{
    public function migrate()
    {
        $tokenAttendu = Configure::read('Security.migration_token');
        $tokenRecu = $this->request->getQuery('token');

        if (!$tokenAttendu || $tokenRecu !== $tokenAttendu) {
            throw new \Cake\Http\Exception\ForbiddenException('Accès refusé.');
        }

        $migrator = new Migrations();
        // Exécute les migrations et capture le résultat
        if ($migrator->migrate()) {
            return $this->response
							->withStatus(200)
							->withStringBody('Migration réussie !');
        }
        
        return $this->response
						->withStatus(500)
						->withStringBody('Erreur de migration.');
    }
}
