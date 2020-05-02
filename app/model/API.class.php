<?php

use Insta\MVPUtils;
use JetPHP\Model\Criptography;
use JetPHP\Model\DB;
use JetPHP\Model\Start;

class API
{
    protected function getInstance()
    {
        return new API();
    }

    protected function like()
    {
        $id_user = Start::session('id_user');
        $id_post = Start::post("id_post");
        if (isset($_POST['id_post'])) {
            $qr = DB::execute("SELECT * FROM post_likes WHERE id_user = ? and id_post = ?", [
                $id_user, $id_post
            ]);
            if ($qr->count() > 0) {
                $qr_deslike = DB::getInstance()->execute("DELETE FROM post_likes WHERE id_user = ? and id_post = ?", [
                    $id_user, $id_post
                ]);
                if ($qr_deslike->count() > 0) {
                    $arr = [
                        'status' => 'deslikeSuccess'
                    ];
                } else {
                    $arr = [
                        'status' => 'deslikeError'
                    ];
                }
            } else {
                $qr_like = DB::getInstance()->execute("INSERT INTO post_likes (id_post, id_user) VALUES ($id_post,$id_user)");
                if ($qr_like->count() > 0) {
                    $arr = [
                        'status' => 'likeSuccess'
                    ];
                } else {
                    $arr = [
                        'status' => 'likeError'
                        
                    ];
                }
            }
            return $arr;
        } else {
            return [
                'status' => 'Nenhum id recebido'
            ];
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
            $pass
        ]);
        if ($qr->count() > 0) {
            $_SESSION['id_user'] = $qr->list(\PDO::FETCH_OBJ)->id;
            return [
                'status' => true,
                'msg'=>'Logado com sucesso'
            ];
        } else {
            return [
                'status' => false,
                'msg'=>'Erro ao logar'
            ];
        }
    }
    public function register()
    {
        $utils = new MVPUtils;
        $telefone = Start::post('telefone');
        $nome = Start::post('nome');
        $usuario = Start::post('usuario');
        $senha = Criptography::md5(Start::post('pass'));

        $usuarioJaExiste = $utils->userExists($telefone, $usuario);
        $tipoCad = 'telefone';
        $email = '';
        if (strstr($telefone, '@')) $tipoCad = 'email';
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
                $senha
            ]);
            if ($qr->count() > 0) {
                $c1 = DB::execute("SELECT LAST_INSERT_ID() as id");
                if ($c1->count() > 0) {
                    $_SESSION['id_user'] = $c1->list(\PDO::FETCH_OBJ)->id;
                }
                return [
                    'status' => true,
                    'msg'=>'Cadastrado com sucesso'
                ];
            } else {
                return [
                    'status' => false,
                    'msg'=>'Erro ao cadastrar-se'
                ];
            }
        }
    }
}
