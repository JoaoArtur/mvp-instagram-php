<?php
namespace Insta;

use JetPHP\Model\Criptography;
use JetPHP\Model\DB;
use JetPHP\Model\Start;

class MVPUtils
{
    private $logado = false;

    public function getData($id)
    {
        $qr = DB::execute("SELECT * FROM usuarios WHERE id = $id");
        if ($qr->count() > 0) {
            return $qr->list(\PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }

    public function userExists($telefone, $usuario)
    {
        $qr = DB::execute("SELECT id FROM usuarios WHERE (telefone = ? or email = ?) or usuario = ?", [
            $telefone,
            $telefone,
            $usuario,
        ]);
        if ($qr->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function login()
    {
        $credencial = Start::post('user');
        $pass = Criptography::md5(Start::post('pass'));

        $qr = DB::execute("SELECT id FROM usuarios WHERE (usuario = ? or email = ? or telefone = ?) and senha = ?", [
            $credencial,
            $credencial,
            $credencial,
            $pass,
        ]);
        if ($qr->count() > 0) {
            $_SESSION['id_user'] = $qr->list(\PDO::FETCH_OBJ)->id;
            return true;
        } else {
            return false;
        }
    }
    public function register()
    {
        $telefone = Start::post('telefone');
        $nome = Start::post('nome');
        $usuario = Start::post('usuario');
        $senha = Criptography::md5(Start::post('pass'));

        $usuarioJaExiste = $this->userExists($telefone, $usuario);
        $tipoCad = 'telefone';
        $email = '';
        if (strstr($telefone, '@')) {
            $tipoCad = 'email';
        }

        $email = $telefone;
        $telefone = '';

        if ($usuarioJaExiste) {
            return false;
        } else {
            $qr = DB::execute("INSERT INTO usuarios (nome, usuario, telefone, email, senha) VALUES (?,?,?,?,?)", [
                $nome,
                $usuario,
                $telefone,
                $email,
                $senha,
            ]);
            if ($qr->count() > 0) {
                $c1 = DB::execute("SELECT LAST_INSERT_ID() as id");
                if ($c1->count() > 0) {
                    $_SESSION['id_user'] = $c1->list(\PDO::FETCH_OBJ)->id;
                }
                return true;
            } else {
                return false;
            }
        }

    }
    public function isOnline()
    {
        if (isset($_SESSION['id_user'])) {
            $this->logado = true;
        }
        return $this->logado;
    }
}
