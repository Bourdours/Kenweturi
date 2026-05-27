<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsBannedToUser extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('user', [
            'is_banned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_admin',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'rejected'],
                'default'    => 'pending',
                'after'      => 'email', 
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
$this->forge->dropColumn('user', ['is_banned', 'status', 'deleted_at']);    }
}