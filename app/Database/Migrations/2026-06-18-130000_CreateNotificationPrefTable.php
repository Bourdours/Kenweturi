<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationPrefTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'pref' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey(['user_id', 'pref']);
        $this->forge->addForeignKey('user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_notification_pref');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_notification_pref');
    }
}
