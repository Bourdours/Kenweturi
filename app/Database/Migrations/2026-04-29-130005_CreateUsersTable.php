<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'firstname'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'lastname'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 320],
            'registered_at'  => ['type' => 'DATETIME'],
            'gender'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'avatar'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'birth_date'     => ['type' => 'DATETIME', 'null' => true],
            'biography'      => ['type' => 'TEXT', 'null' => true],
            'is_admin'       => ['type' => 'BOOLEAN'],
            'password_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone_notif'    => ['type' => 'BOOLEAN'],
            'email_notif'    => ['type' => 'BOOLEAN'],
            'user_status_id' => ['type' => 'INT'],
            'city_id'        => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addForeignKey('user_status_id', 'user_statuses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('city_id', 'cities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('users');
    }
}
