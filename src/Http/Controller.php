<?php

namespace HCTorres02\SimpleAPI\Http;

use HCTorres02\SimpleAPI\Model\Model;

class Controller
{
    public static function get_response(Request $request, Model $model)
    {
        switch ($request->method) {
            case 'GET':
                $data = $model->select();

                if ($model->id && !$data) {
                    return [
                        'code' => 404,
                        'data' => "{$model->table->name} {$model->id} doesn't exist"
                    ];
                }

                return [
                    'code' => 200,
                    'data' => $data
                ];
                break;

            case 'POST':
                $data = $model->create();

                return [
                    'code' => 201,
                    'data' => $data
                ];
                break;

            case 'PUT':
                $data = $model->update();

                if (!$data) {
                    return [
                        'code' => 404,
                        'data' => "{$model->table->name} {$model->id} doesn't exists!"
                    ];
                }

                return [
                    'code' => 200,
                    'data' => true
                ];
                break;

            case 'DELETE':
                $data = $model->destroy();

                if (!$data) {
                    return [
                        'code' => 404,
                        'data' => "{$model->table->name} {$model->id} doesn't exists!"
                    ];
                }

                return [
                    'code' => 200,
                    'data' => true
                ];
                break;

            default:
                return [
                    'code' => 405,
                    'data' => 'Method not allowed'
                ];
                break;
        }
    }
}
