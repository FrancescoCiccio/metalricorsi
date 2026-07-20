<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $tables = [
        'courses' => ['title', 'description'],
        'videos' => ['title', 'description'],
        'downloads' => ['title', 'description'],
    ];

    public function up(): void
    {
        // title è string(255): il JSON con più lingue può superarla
        foreach (array_keys($this->tables) as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->text('title')->change();
            });
        }

        foreach ($this->tables as $table => $columns) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $update = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if ($value !== null && json_decode($value, true) === null) {
                            $update[$column] = json_encode(['it' => $value], JSON_UNESCAPED_UNICODE);
                        }
                    }
                    if ($update !== []) {
                        DB::table($table)->where('id', $row->id)->update($update);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $columns) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $update = [];
                    foreach ($columns as $column) {
                        $decoded = json_decode($row->{$column} ?? '', true);
                        if (is_array($decoded)) {
                            $update[$column] = $decoded['it'] ?? reset($decoded);
                        }
                    }
                    if ($update !== []) {
                        DB::table($table)->where('id', $row->id)->update($update);
                    }
                }
            });
        }
    }
};
