<?php

namespace HCTorres02\SimpleAPI;

class Model
{
    public $id;
    public $host_tb;
    public $host_cols;
    public $foreign_tb;
    public $foreign_cols;
    public $foreign_refs;
    public $data;

    public function __construct(Request $request)
    {
        $this->id = $request->id;
        $this->host_tb = $request->table;

        if (!$request->is_get) {
            $this->host_cols = $request->data_cols;
            $this->data = $request->data;

            return;
        }

        $this->host_cols = get_columns(
            $request->table,
            $request->is_get,
            $request->is_get
        );

        if ($request->foreign) {
            $this->foreign_tb = $request->foreign;
            $this->foreign_cols = get_columns(
                $request->foreign,
                $request->is_get,
                $request->is_get
            );
            $this->foreign_refs = implode(' = ', Session::get(
                'references',
                $request->foreign,
                $request->table
            ));
        }
    }
}
