<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeCityIdNullableOnUser extends Migration
{
    public function up()
    {
        // Supprime la FK 
        $this->db->query('ALTER TABLE user DROP FOREIGN KEY user_city_id_foreign');

        $this->forge->modifyColumn('user', [
            'city_id' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('user', [
            'city_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
    }
}