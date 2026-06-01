<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'admin', 'superadmin'],
                'default'    => 'user',
                'null'       => false,
                'after'      => 'is_admin',
            ],
        ]);

        $this->db->query("UPDATE user SET role = 'admin' WHERE is_admin = 1");
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'role');
    }
}
