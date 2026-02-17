<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class FixPostsTranslationsToShadow extends BaseMigration
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
        $table = $this->table('posts_translations');

        // 1. Suppression des colonnes de l'ancienne stratégie EAV
        $table->removeIndex(['locale', 'model', 'foreign_key', 'field']);
        $table->removeColumn('model')
              ->removeColumn('foreign_key')
              ->removeColumn('field')
              ->removeColumn('content');

		$table->changeColumn('id', 'integer', [
			'identity' => false, // Désactive l'auto-incrément (spécifique Phinx)
			'null' => false
		]);

        // 2. Ajout des colonnes spécifiques pour la stratégie Shadow
        $table->addColumn('title', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('body', 'text', ['null' => true])
              ->addColumn('slug', 'string', ['limit' => 255, 'null' => true])
              ->update();

        // 3. Définition de la clé primaire composite (id + locale)
        // Note: On s'assure que 'id' correspond à l'ID du post original
        $table->addIndex(['id', 'locale'], ['unique' => true])
              ->update();
    }
}
