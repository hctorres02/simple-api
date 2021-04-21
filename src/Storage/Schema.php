<?php

namespace HCTorres02\SimpleAPI\Storage;

use HCTorres02\SimpleAPI\Model\TableModel;
use HCTorres02\SimpleAPI\Storage\Database;
use stdClass;

class Schema
{
    public const ALL = '*';
    public const SCHEMA = 'schema';
    public const SCHEMA_REFERENCES = 'schema_references';

    /**
     * @var Database
     */
    private $db;

    /**
     * @var array
     */
    private $schemas;

    public function __construct(Database $db)
    {
        $this->db = $db;

        if (empty($this->get_all_schemas())) {
            $this->build();
        }

        $this->schemas = $this->get_all_schemas();
    }

    public function get_schema(string $table): TableModel
    {
        return $this->schemas[$table];
    }

    public function has_schema(?string $table): bool
    {
        return array_key_exists($table, $this->schemas);
    }

    private function get_all_schemas(): ?array
    {
        return $_SESSION[self::SCHEMA] ?? null;
    }

    private function build(): void
    {
        $tables = $this->build_tables();
        $references = $this->build_references();
        $excluded = $this->db->excluded;

        foreach ($tables as $table => $columns) {
            $schema[$table] = new TableModel([
                'name' => $table,
                'columns' => $columns,
                'alias' => $this->db->get_alias($table),
                'references' => $references[$table] ?? [],
                'excluded' => $excluded
            ]);
        }

        $_SESSION[self::SCHEMA] = $schema;
    }

    private function build_tables(): array
    {
        $tables = [];
        $columns = [
            'table_name',
            'column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.columns')
            ->where('table_schema', $this->db->dbname)
            ->order_by('table_name', 'ordinal_position');

        $result = $this->db->select($query);

        foreach ($result as $row) {
            $tb_name = strtolower($row['table_name']);
            $col_name = strtolower($row['column_name']);

            $tables[$tb_name][] = $col_name;
        }

        return $tables;
    }

    private function build_references(): array
    {
        $references = [];
        $columns = [
            'table_name',
            'column_name',
            'referenced_table_name',
            'referenced_column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.key_column_usage')
            ->where_is('referenced_table_name', 'NOT NULL')
            ->and('table_schema', $this->db->dbname);

        $result = $this->db->select($query);

        foreach ($result as $row) {
            $tb_name = $row['table_name'];
            $col_name = $row['column_name'];
            $ref_tb_name = $row['referenced_table_name'];
            $ref_col_name = $row['referenced_column_name'];

            $references[$tb_name][$ref_tb_name] = [
                "{$tb_name}.{$col_name}",
                "{$ref_tb_name}.{$ref_col_name}"
            ];
        }

        return $references;
    }
}
