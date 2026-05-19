<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'registered_at',
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'deleted_at');
    }
}
