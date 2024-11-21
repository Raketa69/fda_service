<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SimpleSearch
{

    public function findFunctionalDependencies(string $tableName)
    {
        
    }


    public function findFunctionalDependenciesInTable(string $tableName)
    {
        $columns = DB::select("SHOW COLUMNS FROM $tableName");
        $columns = array_column($columns, 'Field');

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

        return response()->json([
            'table' => $tableName,
            'dependencies' => $dependencies
        ]);
    }

    private function checkDependency(string $tableName, string $determinant, string $dependent): bool
    {
        // Подсчитываем количество уникальных комбинаций determinant -> dependent
        $groupedCount = DB::table($tableName)
            ->selectRaw("$determinant, $dependent")
            ->groupBy($determinant, $dependent)
            ->count();

        // Подсчитываем количество уникальных значений determinant
        $determinantCount = DB::table($tableName)
            ->selectRaw($determinant)
            ->groupBy($determinant)
            ->count();

        // Проверяем, совпадает ли количество групп
        return $groupedCount === $determinantCount;
    }
}
