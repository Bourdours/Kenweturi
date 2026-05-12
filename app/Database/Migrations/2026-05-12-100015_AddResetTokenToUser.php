<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetTokenToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'password_hash',
            ],
            'reset_token_expiry' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'reset_token',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user', ['reset_token', 'reset_token_expiry']);
    }
}
