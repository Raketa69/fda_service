<?php

declare(strict_types=1);

namespace App\Services\SimpleSearch;

use App\Services\DatabaseHelper;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class SearchService
{
    private Connection $connection;

    public function findFunctionalDependencies(Connection $connection): array
    {
        $result = [];
        $this->connection = $connection;

        $tables = (new DatabaseHelper($connection))->getDatabaseTables();

        foreach ($tables as $table) {
            $result[] = $this->findFunctionalDependenciesInTable($table);
        }

        return $result;
    }

    public function findFunctionalDependenciesInTable(string $tableName)
    {
        // Получаем список столбцов таблицы из information_schema.columns
        $columns = DB::connection($this->connection->getName())
            ->table('information_schema.columns')
            ->select('column_name')
            ->where('table_name', $tableName)
            ->where('table_schema', 'public') // Задаем схему, если нужно
            ->pluck('column_name')
            ->filter(function ($value) {
                return !in_array($value, ['createdAt', 'last_update', 'create_date', 'updatedAt', 'cteated_at', 'updated_at']);
            })
            ->toArray();


        if (empty($columns)) {
            return response()->json(['message' => "Таблица $tableName не содержит столбцов."], 400);
        }

        $dependencies = [];

        // Перебираем пары столбцов для проверки зависимостей
        foreach ($columns as $determinant) {
            foreach ($columns as $dependent) {
                if ($determinant === $dependent) {
                    continue; // Пропускаем одинаковые столбцы
                }

                if ($this->checkDependency($tableName, $determinant, $dependent)) {
                    $dependencies[] = [
                        'determinant' => $determinant,
                        'dependent' => $dependent
                    ];
                }
            }
        }

        return [
            'table' => $tableName,
            'dependencies' => $dependencies
        ];
    }

    private function checkDependency(string $tableName, string $determinant, string $dependent): bool
    {
        // Подсчитываем количество уникальных комбинаций determinant -> dependent
        $groupedCount = DB::connection($this->connection->getName())
            ->table($tableName)
            ->selectRaw("$determinant, $dependent")
            ->groupBy($determinant, $dependent)

            ->count();

        // Подсчитываем количество уникальных значений determinant
        $determinantCount = DB::connection($this->connection->getName())
            ->table($tableName)
            ->selectRaw($determinant)
            ->groupBy($determinant)
            ->count();

        // Проверяем, совпадает ли количество групп
        if ($groupedCount === $determinantCount) {
            return true;
        } else {

            return $this->calculateInaccuracy($groupedCount, $determinantCount) > 0.1;
        }
    }

    private function calculateInaccuracy(int|float $grouped, int|float $determinant): float
    {
        return ($grouped) / (($determinant ** 2) - $determinant);
    }
}
