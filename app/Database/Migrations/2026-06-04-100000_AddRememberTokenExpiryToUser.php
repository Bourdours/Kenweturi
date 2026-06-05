<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRememberTokenExpiryToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'remember_token_expiry' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'remember_token',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'remember_token_expiry');
    }
}
