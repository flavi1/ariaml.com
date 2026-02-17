<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePostsTranslations extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
		
		
		// 1. Suppression de l'ancienne table i18n
        if ($this->hasTable('i18n')) {
            $this->table('i18n')->drop()->save();
        }

        // 2. Création de la table dédiée
		$table = $this->table('posts_translations', ['id' => false, 'primary_key' => ['id', 'locale']]);
        $table->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false])
              ->addColumn('locale', 'string', ['limit' => 6, 'null' => false])
              // On ajoute une colonne par champ traduisible
              ->addColumn('title', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('slug', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('body', 'text', ['null' => true])
              ->create();
    }
}
