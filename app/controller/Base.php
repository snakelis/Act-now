<?php

namespace app\controller;

use app\BaseController;

class Base extends BaseController
{

    public function success_json($data = [], $msg = '')
    {
        header('Content-type: application/json');

        $response = [
            'status' => '1',
            'data' => $data,
            'msg' => $msg,

        ];
        return json($response);
    }

    public function error_json($msg = '', $data = [])
    {
        header('Content-type: application/json');

        $response = [
            'status' => '0',
            'msg' => $msg,
            'data' => $data,
        ];
        return json($response);
    }

}
