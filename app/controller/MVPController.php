<?php
    use JetPHP\Model\Start;

    class MVPController extends Controle {
        public static function api() {
            header('Content-type: application/json');
            $p = Start::get('pagina');
            if (file_exists("../app/view/api/$p.php")) {
                include "../app/view/api/$p.php";
            }
        }
        public static function login() {
            return self::view('insta.login', null, false, false);
        }
        public static function register() {
            return self::view('insta.register', null, false, false);
        }

        public static function account() {
            $tipo = Start::get('tipo');

            switch ($tipo) {
                case 'login':
                    return self::login();
                    break;
                case 'register':
                    return self::register();
                    break;
                default:
                    header("Location:/");

            }

        }

    }