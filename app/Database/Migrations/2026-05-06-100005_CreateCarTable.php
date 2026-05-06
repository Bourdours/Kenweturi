<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCarTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'seats' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'user', 'id');
        $this->forge->createTable('car');
    }

    public function down()
    {
        $this->forge->dropTable('car');
    }
}
