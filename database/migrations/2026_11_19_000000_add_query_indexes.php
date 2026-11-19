<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Indexes for the filters every public page runs. Adding an index is online
// on MySQL 8 (InnoDB, no table lock) and touches no data.
return new class extends Migration
{
    private array $indexes = [
        'documents' => [['category', 'is_published']],
        'news' => [['is_published', 'published_at'], ['is_headline']],
        'galleries' => [['is_published', 'created_at']],
        'gallery_items' => [['gallery_id', 'order']],
        'events' => [['start_date']],
        'officials' => [['is_published', 'order']],
        'forms_requests' => [['status']],
        'forms_complaints' => [['status']],
        'contacts' => [['is_read']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $list) {
            Schema::table($table, function (Blueprint $t) use ($list) {
                foreach ($list as $columns) {
                    $t->index($columns);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $list) {
            Schema::table($table, function (Blueprint $t) use ($list) {
                foreach ($list as $columns) {
                    $t->dropIndex($columns);
                }
            });
        }
    }
};
