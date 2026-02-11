<?php
declare(strict_types=1);

use Migrations\BaseMigration; 

class Initial extends BaseMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        // Table Posts
        if (!$this->hasTable('posts')) {
            $this->table('posts')
                ->addColumn('parent_id', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true,
                    'signed' => false,
                ])
                ->addColumn('lft', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true,
                    'signed' => false,
                ])
                ->addColumn('rght', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true,
                    'signed' => false,
                ])
                ->addColumn('type', 'string', [
                    'limit' => 255, 
                    'null' => false, 
                    'default' => 'page'
                ])
                ->addColumn('format', 'string', [
                    'limit' => 255, 
                    'null' => false, 
                    'default' => 'markdown'
                ])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('body', 'text', ['null' => false])
                ->addColumn('published', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('created', 'datetime', ['null' => true])
                ->addColumn('modified', 'datetime', ['null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['parent_id'])
                // Ajout d'une contrainte d'intégrité pour le parent
                ->addForeignKey('parent_id', 'posts', 'id', [
                    'delete' => 'SET_NULL',
                    'update' => 'CASCADE'
                ])
                ->create();
        }

        // Table i18n
        if (!$this->hasTable('i18n')) {
            $this->table('i18n')
                ->addColumn('locale', 'string', ['limit' => 6, 'null' => false])
                ->addColumn('model', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('foreign_key', 'integer', ['limit' => 11, 'null' => false, 'signed' => false])
                ->addColumn('field', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('content', 'text', ['null' => true])
                ->addIndex(['model', 'foreign_key', 'locale', 'field'], [
                    'name' => 'I18N_LOCALE_FIELD',
                    'unique' => true
                ])
                ->addIndex(['model', 'foreign_key', 'field'], [
                    'name' => 'I18N_FIELD'
                ])
                ->create();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        // Note : Supprimer les clés étrangères n'est pas nécessaire si on drop la table
        $this->table('posts')->drop()->save();
        $this->table('i18n')->drop()->save();
    }
}
