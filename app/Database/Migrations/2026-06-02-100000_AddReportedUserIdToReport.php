<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportedUserIdToReport extends Migration
{
    public function up()
    {
        $this->forge->addColumn('report', [
            'reported_user_id' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => null,
                'after'      => 'user_id',
            ],
        ]);

        $this->db->query('ALTER TABLE report ADD CONSTRAINT fk_report_reported_user FOREIGN KEY (reported_user_id) REFERENCES user(id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE report DROP FOREIGN KEY fk_report_reported_user');
        $this->forge->dropColumn('report', 'reported_user_id');
    }
}
