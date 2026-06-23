<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationToUser extends Migration
{
    public function up(): void
    {
        // Ajoute 'unverified' comme premier statut possible (avant 'pending')
        $this->forge->modifyColumn('user', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['unverified', 'pending', 'active', 'rejected'],
                'default'    => 'unverified',
                'null'       => false,
            ],
        ]);

        $this->forge->addColumn('user', [
            'email_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status',
            ],
            'email_token_expiry' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'email_token',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user', ['email_token', 'email_token_expiry']);

        $this->forge->modifyColumn('user', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'rejected'],
                'default'    => 'pending',
                'null'       => false,
            ],
        ]);
    }
}
