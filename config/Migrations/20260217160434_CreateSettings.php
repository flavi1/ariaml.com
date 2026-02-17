<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateSettings extends BaseMigration
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
		$table = $this->table('settings');
		$table->addColumn('name', 'string', ['limit' => 100, 'null' => false])
			  ->addColumn('value', 'text', ['null' => true])
			  ->addIndex(['name'], ['unique' => true])
			  ->create();
    }
}
