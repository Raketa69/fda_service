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
        foreach ($dependencies as &$dependency) {

            foreach ($dependency['dependencies'] as &$value) {

                if ($this->isPartialDependency($value, $dependency['table'])) {
                    $value['type'] = 'partial';
                }

                if ($this->isTransitiveDependency($value, $dependency['table'], $dependency['dependencies'])) {

                    if (!isset($value['type'])) {
                        $value['type'] = 'transitive';
                    } else {
                        $value['type'] .= ', transitive';
                    }
                }

                if (!isset($value['type'])) {
                    $value['type'] = '-';
                }
            }
        }

        return $dependencies;
    }

    protected function isPartialDependency(array $dependency, $table): bool
    {
        // Получаем список атрибутов первичного ключа таблицы
        $primaryKey = $this->getPrimaryKeyAttributes($table);

        $determinant = explode(',', $dependency['determinant']);

        if (
            !empty(array_intersect($determinant, $primaryKey))
            && count(array_intersect($determinant, $primaryKey)) < count($primaryKey)
        ) {
            return true;
        };


        return false;
    }

    protected function getPrimaryKeyAttributes(string $tableName): array
    {
        // Параметризованный запрос
        $query = "
            SELECT distinct a.attname
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

    protected function isTransitiveDependency(array $dependency, $table, $dependencies): bool
    {

        $determinant = $dependency['determinant'];
        $dependent = $dependency['dependent'];

        // Проверяем, существует ли промежуточная зависимость
        foreach ($dependencies as $intermediateDependency) {
            $intermediate = $intermediateDependency['determinant'];

            // Проверяем: determinant → intermediate и intermediate → dependent
            if (
                $intermediate !== $determinant &&
                $intermediateDependency['dependent'] === $dependent &&
                $this->checkDependency($table, $determinant, $intermediate)
            ) {
                // Проверяем, что intermediate не является частью ключа
                if (!$this->isPartOfPrimaryKey($table, $intermediate)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isPartOfPrimaryKey(string $tableName, string $attribute): bool
    {
        $primaryKey = $this->getPrimaryKeyAttributes($tableName);
        return in_array($attribute, $primaryKey);
    }

    protected function checkDependency(string $tableName, string $determinant, string $dependent): bool
    {
        // SQL для проверки уникальности пар determinant -> dependent
        $query = "
            SELECT COUNT(DISTINCT '$determinant') AS unique_determinants,
                   COUNT(*) AS unique_pairs
            FROM (
                SELECT DISTINCT  '$determinant', '$dependent'
                FROM  $tableName
            ) AS subquery;
        ";

        // Выполнение запроса
        $result = DB::connection($this->connection->getName())
            ->select($query);

        // DD($result);

        return $result[0]->unique_determinants === $result[0]->unique_pairs;
    }

    function analyzeDependencies(array $dependencies, string $tableName)
    {
        $violations = [];
        $recommendations = [];

        foreach ($dependencies as $dependency) {
            $determinant = $dependency['determinant'];
            $dependent = $dependency['dependent'];
            $type = explode(',', $dependency['type']); // Распознаем типы зависимости

            // Проверяем на нарушение BCNF
            if (in_array('partial', $type) || in_array('transitive', $type)) {
                $violations[] = [
                    'determinant' => $determinant,
                    'dependent' => $dependent,
                    'type' => $type,
                    'issue' => 'BCNF violation',
                ];

                $recommendations[] = [
                    'action' => 'decompose',
                    'reason' => 'To achieve BCNF',
                    'new_table' => [$determinant, $dependent],
                ];
            }

            // Проверяем на нарушение 3NF
            if (in_array('transitive', $type)) {
                $violations[] = [
                    'determinant' => $determinant,
                    'dependent' => $dependent,
                    'type' => $type,
                    'issue' => '3NF violation',
                ];

                $recommendations[] = [
                    'action' => 'decompose',
                    'reason' => 'To achieve 3NF',
                    'new_table' => [$determinant, $dependent],
                ];
            }
        }

        return [
            'table' => $tableName,
            'violations' => $violations,
            'recommendations' => $recommendations,
        ];
    }
}
