<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddModerationFieldsToReport extends Migration
{
    public function up(): void
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['open', 'processing', 'closed'],
                'default'    => 'open',
                'after'      => 'user_id',
            ],
            'admin_comment' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
            ],
            'resolved_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'admin_comment',
            ],
            'resolved_by' => [
                'type'  => 'INT',
                'null'  => true,
                'after' => 'resolved_at',
            ],
        ];

        $this->forge->addColumn('report', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('report', ['status', 'admin_comment', 'resolved_at', 'resolved_by']);
    }
}