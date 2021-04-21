<?php

namespace HCTorres02\SimpleAPI\Model;

class TableModel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var array
     */
    public $columns;

    /**
     * @var array
     */
    public $columns_filtered;

    /**
     * @var array
     */
    private $references;

    /**
     * @var array
     */
    private $excluded;

    public function __construct(array $props)
    {
        foreach ($props as $key => $value) {
            $this->{$key} = $value;
        }

        $this->columns_filtered = array_diff($this->columns, $this->excluded);
    }

    public function get_reference(string $table): array
    {
        return $this->references[$table];
    }

    public function has_reference(?string $table): bool
    {
        return array_key_exists($table, $this->references);
    }
}
