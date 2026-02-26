<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Schema;

trait ColumnSafeSeeder
{
    protected function tableColumns(string $table): array
    {
        static $cache = [];

        if (!isset($cache[$table])) {
            $cache[$table] = Schema::getColumnListing($table);
        }

        return $cache[$table];
    }

    protected function filterRowByTable(string $table, array $row): array
    {
        $columns = $this->tableColumns($table);

        return array_intersect_key($row, array_flip($columns));
    }

    protected function filterRowsByTable(string $table, array $rows): array
    {
        $filteredRows = [];

        foreach ($rows as $row) {
            $filtered = $this->filterRowByTable($table, $row);

            if (!empty($filtered)) {
                $filteredRows[] = $filtered;
            }
        }

        return $filteredRows;
    }
}
