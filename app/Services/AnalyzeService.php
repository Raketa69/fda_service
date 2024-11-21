<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class AnalyzeService
{
    public function __construct(
        private Connection $connection
    ) {}

    public function analyzeNormalForms(array $dependencies): array
    {
        $violations = [];
        foreach ($dependencies as $dependency) {

            // Пример проверки: Частичные и транзитивные зависимости
            if ($this->isPartialDependency($dependency)) {
                $violations[] = [
                    'type' => 'Partial Dependency',
                    'dependency' => $dependency,
                ];

                // if ($this->isTransitiveDependency($dependency)) {
                //     $violations[] = [
                //         'type' => 'Transitive Dependency',
                //         'dependency' => $dependency,
                //     ];
                // }

            }
        }
        return $violations;
    }

    protected function isPartialDependency(array $dependencys): bool
    {
        // Получаем список атрибутов первичного ключа таблицы
        $primaryKey = $this->getPrimaryKeyAttributes($dependencys['table']);

        // Если determinant является частью первичного ключа, проверяем
        foreach ($dependencys["dependencies"] as $dependency) {

            $determinant = explode(',', $dependency['determinant']);

            if (
                !empty(array_intersect($determinant, $primaryKey))
                && count(array_intersect($determinant, $primaryKey)) < count($primaryKey)
            ) {
                return true;
            };
        }

        return false;
    }

    protected function getPrimaryKeyAttributes(string $tableName): array
    {
        // Параметризованный запрос
        $query = "
            SELECT a.attname
            FROM pg_index i
            JOIN pg_attribute a ON a.attnum = ANY(i.indkey)
            WHERE i.indrelid = :tableName::regclass AND i.indisprimary;
        ";

        // Выполнение запроса
        $result = DB::connection($this->connection->getName())
            ->select($query, ['tableName' => $tableName]);

        // Возвращаем список столбцов
        return array_column($result, 'attname');
    }
}
