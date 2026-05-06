<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocationTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,5',
                'null'       => false,
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,5',
                'null'       => false,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],
            'city_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('city_id', 'city', 'id');
        $this->forge->createTable('location');
    }

    public function down()
    {
        $this->forge->dropTable('location');
    }
}
