<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCityTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'zipcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('city');
    }

    public function down()
    {
        $this->forge->dropTable('city');
    }
}
