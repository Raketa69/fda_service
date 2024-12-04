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

    public function analyzeDependencies(array $dependencies, string $tableName)
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

    /**--------------------------------------------------- */

    public function getMainAnalytic($connection): array
    {
        $result =  DB::connection($connection->getName())
            ->select(
                "SELECT 
                        table_schema AS schema_name,
                        table_name,
                        COUNT(*) AS column_count,
                        pg_relation_size(pg_class.oid) AS table_size_bytes,
                        COALESCE(seq_scan, 0) AS sequential_scans,
                        COALESCE(idx_scan, 0) AS index_scans,
                        COALESCE(n_live_tup, 0) AS live_rows_estimate,
                        COALESCE(n_dead_tup, 0) AS dead_rows_estimate,
                        (SELECT COUNT(*) 
                        FROM information_schema.table_constraints tc
                        WHERE tc.table_schema = c.table_schema 
                        AND tc.table_name = c.table_name 
                        AND tc.constraint_type = 'PRIMARY KEY') AS primary_key_count,
                        (SELECT COUNT(*) 
                        FROM information_schema.table_constraints tc
                        WHERE tc.table_schema = c.table_schema 
                        AND tc.table_name = c.table_name 
                        AND tc.constraint_type = 'FOREIGN KEY') AS foreign_key_count
                    FROM 
                        information_schema.columns c
                    LEFT JOIN 
                        pg_stat_user_tables psut ON c.table_name = psut.relname AND c.table_schema = psut.schemaname
                    LEFT JOIN 
                        pg_class ON psut.relid = pg_class.oid
                    WHERE 
                        c.table_schema NOT IN ('information_schema', 'pg_catalog')
                    GROUP BY 
                        c.table_schema, c.table_name, pg_class.oid, seq_scan, idx_scan, n_live_tup, n_dead_tup
                    ORDER BY 
                        table_schema, table_name;
                    "
            );

        return $result;
    }

    public function tableSizes()
    {
        $sizes = DB::select("
         SELECT
             relname AS table_name,
             pg_size_pretty(pg_total_relation_size(relid)) AS total_size
         FROM
             pg_catalog.pg_statio_user_tables
         ORDER BY
             pg_total_relation_size(relid) DESC;
     ");

        return view('analytics.table_sizes', ['sizes' => $sizes]);
    }

    // Метод для получения количества строк в таблицах
    public function rowCounts()
    {
        $counts = DB::select("
         SELECT
             schemaname,
             relname AS table_name,
             n_live_tup AS row_count
         FROM
             pg_stat_user_tables
         ORDER BY
             n_live_tup DESC;
     ");

        return view('analytics.row_counts', ['counts' => $counts]);
    }

    // Метод для получения информации об индексах
    public function indexInfo()
    {
        $indexes = DB::select("
         SELECT
             t.relname AS table_name,
             i.relname AS index_name,
             pg_size_pretty(pg_relation_size(i.oid)) AS index_size
         FROM
             pg_class t,
             pg_class i,
             pg_index ix,
             pg_namespace n
         WHERE
             t.oid = ix.indrelid
             AND i.oid = ix.indexrelid
             AND t.relkind = 'r'
             AND t.relnamespace = n.oid
             AND n.nspname = 'public'
         ORDER BY
             pg_relation_size(i.oid) DESC;
     ");

        return view('analytics.index_info', ['indexes' => $indexes]);
    }

    // Метод для получения списка таблиц и их описаний
    public function tableDescriptions()
    {
        $tables = DB::select("
         SELECT
             c.relname AS table_name,
             pg_catalog.obj_description(c.oid, 'pg_class') AS description
         FROM
             pg_catalog.pg_class c
         LEFT JOIN
             pg_catalog.pg_namespace n ON n.oid = c.relnamespace
         WHERE
             c.relkind = 'r'
             AND n.nspname = 'public'
         ORDER BY
             c.relname;
     ");

        return view('analytics.table_descriptions', ['tables' => $tables]);
    }

    // Метод для получения информации о столбцах
    public function columnInfo()
    {
        $columns = DB::select("
         SELECT
             table_name,
             column_name,
             data_type,
             is_nullable,
             character_maximum_length
         FROM
             information_schema.columns
         WHERE
             table_schema = 'public'
         ORDER BY
             table_name, ordinal_position;
     ");

        return view('analytics.column_info', ['columns' => $columns]);
    }

    // Метод для получения информации о внешних ключах
    public function foreignKeys()
    {
        $foreignKeys = DB::select("
         SELECT
             tc.table_name,
             kcu.column_name,
             ccu.table_name AS foreign_table_name,
             ccu.column_name AS foreign_column_name
         FROM
             information_schema.table_constraints AS tc
             JOIN information_schema.key_column_usage AS kcu
               ON tc.constraint_name = kcu.constraint_name
               AND tc.table_schema = kcu.table_schema
             JOIN information_schema.constraint_column_usage AS ccu
               ON ccu.constraint_name = tc.constraint_name
               AND ccu.table_schema = tc.table_schema
         WHERE
             tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public';
     ");

        return view('analytics.foreign_keys', ['foreignKeys' => $foreignKeys]);
    }

    // Метод для получения активных блокировок и транзакций
    public function locks()
    {
        $locks = DB::select("
         SELECT
             l.pid,
             a.usename,
             l.locktype,
             l.mode,
             l.granted,
             a.query
         FROM
             pg_locks l
             JOIN pg_stat_activity a ON l.pid = a.pid
         WHERE
             a.datname = current_database();
     ");

        return view('analytics.locks', ['locks' => $locks]);
    }

    // Метод для получения статистики использования индексов
    public function indexUsage()
    {
        $indexUsage = DB::select("
         SELECT
             relname AS table_name,
             indexrelname AS index_name,
             idx_scan,
             idx_tup_read,
             idx_tup_fetch
         FROM
             pg_stat_user_indexes
         ORDER BY
             idx_scan DESC;
     ");

        return view('analytics.index_usage', ['indexUsage' => $indexUsage]);
    }
}
