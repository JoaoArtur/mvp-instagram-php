<?php

use Insta\MVPUtils;
use JetPHP\Model\Start;

class APIController extends API
{
    public static function index()
    {
        // $_GET['p']
        $p = Start::get('p');
        $utils = new MVPUtils;
        if ($utils->isOnline()) {
            if (method_exists(self::getInstance(), $p)) {
                echo json_encode(self::$p());
            } else {
                echo json_decode([
                    'status'=>false,
                    'msg'=>'Rota inexistente'
                ]);
            }
        } else {
            if ($p == 'login') {
                echo json_encode(self::login());
            } else if ($p == 'register') {
                echo json_encode(self::register());
            } else {
                exit;
            }
        }
    }
}
