<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBooksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'user_id'    => ['type' => 'INT', 'unsigned' => false],
            'journey_id' => ['type' => 'INT', 'unsigned' => false],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey(['user_id', 'journey_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('journey_id', 'journeys', 'id', 'CASCADE', 'CASCADE');

        // Force le moteur InnoDB, requis pour les clés étrangères
        $this->forge->createTable('books', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('books');
    }
}
