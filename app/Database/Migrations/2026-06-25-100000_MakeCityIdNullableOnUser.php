<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeCityIdNullableOnUser extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('user', [
            'city_id' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('user', [
            'city_id' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => null,
            ],
        ]);
    }
}
