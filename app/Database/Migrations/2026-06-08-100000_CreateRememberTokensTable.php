<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRememberTokensTable extends Migration
{
    public function up()
    {
        // Création de la nouvelle table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('remember_tokens');

        // Suppression sécurisée des anciennes colonnes dans user (évite les crashs chez les collègues)
        if ($this->db->fieldExists('remember_token', 'user')) {
            $this->forge->dropColumn('user', 'remember_token');
        }
        if ($this->db->fieldExists('remember_token_expiry', 'user')) {
            $this->forge->dropColumn('user', 'remember_token_expiry');
        }
    }

    public function down()
    {
        $this->forge->dropTable('remember_tokens');

        // Restauration sécurisée des colonnes si elles n'existent plus
        if (!$this->db->fieldExists('remember_token', 'user')) {
            $this->forge->addColumn('user', [
                'remember_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'password_hash',
                ],
            ]);
        }

        if (!$this->db->fieldExists('remember_token_expiry', 'user')) {
            $this->forge->addColumn('user', [
                'remember_token_expiry' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'remember_token',
                ],
            ]);
        }
    }
}