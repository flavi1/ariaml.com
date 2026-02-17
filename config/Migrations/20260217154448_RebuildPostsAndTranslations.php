<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RebuildPostsAndTranslations extends BaseMigration
{
	public function up(): void
    {
        // 1. Suppression sécurisée (l'ordre importe à cause des contraintes)
        if ($this->hasTable('posts_translations')) {
            $this->table('posts_translations')->drop()->save();
        }
        if ($this->hasTable('posts')) {
            $this->table('posts')->drop()->save();
        }

        // 2. Création de la table 'posts'
        $posts = $this->table('posts');
        $posts->addColumn('parent_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null])
              ->addColumn('lft', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null])
              ->addColumn('rght', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null])
              ->addColumn('type', 'string', ['limit' => 255, 'null' => false, 'default' => 'page'])
              ->addColumn('format', 'string', ['limit' => 255, 'null' => false, 'default' => 'markdown'])
              ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('description', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('body', 'text', ['null' => false])
              ->addColumn('published', 'boolean', ['null' => false, 'default' => false])
              ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
              ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
              ->addIndex(['parent_id'])
              ->addIndex(['lft', 'rght'])
              ->create();

        // 3. Création de la table 'posts_translations' (Shadow Table)
        // Note: On désactive l'id auto-incrémenté pour la table de traduction
        $translations = $this->table('posts_translations', ['id' => false, 'primary_key' => ['id', 'locale']]);
        $translations->addColumn('id', 'integer', ['null' => false])
                     ->addColumn('locale', 'string', ['limit' => 6, 'null' => false])
                     ->addColumn('title', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                     ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                     ->addColumn('body', 'text', ['null' => true, 'default' => null])
                     ->addColumn('slug', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                     ->addIndex(['id'])
                     ->create();
    }

    public function down(): void
    {
        $this->table('posts_translations')->drop()->save();
        $this->table('posts')->drop()->save();
    }
}
