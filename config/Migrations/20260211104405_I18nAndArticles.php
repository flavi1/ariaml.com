<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class I18nAndArticles extends BaseMigration
{
    public function up(): void
    {
        // Table Articles
        if (!$this->hasTable('articles')) {
            $this->table('articles')
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('body', 'text', ['null' => false])
                ->addColumn('published', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('created', 'datetime', ['null' => true]) // Permettre null pour laisser Cake gérer
                ->addColumn('modified', 'datetime', ['null' => true])
                ->addIndex(['slug'], ['unique' => true]) // Un index sur le slug est tjs utile
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

    public function down(): void
    {
        $this->table('articles')->drop()->save();
        $this->table('i18n')->drop()->save();
    }
}
