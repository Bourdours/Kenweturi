<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedStatusToUser extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['unverified', 'pending', 'active', 'rejected', 'deleted'],
                'null'       => false,
                'default'    => 'unverified',
            ],
        ];

        $this->forge->modifyColumn('user', $fields);
    }

    public function down()
    {
        // Avant de réduire l'ENUM, on bascule les éventuelles lignes 'deleted'
        // vers une valeur encore valide pour éviter une erreur en mode SQL strict.
        $this->db->table('user')
                 ->where('status', 'deleted')
                 ->update(['status' => 'rejected']);

        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['unverified', 'pending', 'active', 'rejected'],
                'null'       => false,
                'default'    => 'unverified',
            ],
        ];

        $this->forge->modifyColumn('user', $fields);
    }
}