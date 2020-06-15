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
    public function setOnesignalId() {
        if (isset($_POST['id'])) {
            $id = Start::post('id');
            $uid = Start::session('id_user');
            $qr = DB::getInstance()->execute("UPDATE usuarios SET onesignal_id = ? WHERE id = ?",[
                $id,$uid
            ]);
            if ($qr->count() > 0) {
                return ['status'=>true];
            } else {
                return ['status'=>false];
            }
        }
    }
    protected function like()
    {
        $mvp = new MVPGram;
        $id_user = Start::session('id_user');
        $id_post = Start::post("id_post");
        if (isset($_POST['id_post'])) {
            $qr = DB::execute("SELECT * FROM post_likes WHERE id_user = ? and id_post = ?", [
                $id_user, $id_post,
            ]);
            if ($qr->count() > 0) {
                $qr_deslike = DB::getInstance()->execute("DELETE FROM post_likes WHERE id_user = ? and id_post = ?", [
                    $id_user, $id_post,
                ]);
                if ($qr_deslike->count() > 0) {
                    $arr = [
                        'status' => 'deslikeSuccess',
                    ];
                } else {
                    $arr = [
                        'status' => 'deslikeError',
                    ];
                }
            } else {
                $qr_like = DB::getInstance()->execute("INSERT INTO post_likes (id_post, id_user) VALUES ($id_post,$id_user)");
                if ($qr_like->count() > 0) {
                    $post = $mvp->getPostById($id_post);
                    $dados = $mvp->getDados();
                    if ($dados->id != $post->id_usuario) {
                        $mvp->newNotification($post->id_usuario, $dados->nome . ' curtiu seu post', '/post/' . $id_post, $dados->foto);
                    }
                    $arr = [
                        'status' => 'likeSuccess',
                    ];
                } else {
                    $arr = [
                        'status' => 'likeError',

                    ];
                }
            }
            return $arr;
        } else {
            return [
                'status' => 'Nenhum id recebido',
            ];
        }
    }
    protected function comment()
    {
        if (isset($_POST['id_post'])) {
            $msg = Start::post('message');
            $id_post = Start::post('id_post');
            $id_user = Start::session('id_user');

            $qr = DB::getInstance()->execute("SELECT * FROM posts WHERE id = ?", [$id_post]);
            if ($qr->count() > 0) {
                if (trim($msg) != '') {
                    $qr_comment = DB::getInstance()->execute(
                        "INSERT INTO post_comments (id_user,id_post,`message`) VALUES (?,?,?)",
                        [$id_user, $id_post, $msg]
                    );
                    if ($qr_comment->count() > 0) {
                        $mvp = new MVPGram;
                        $post = $mvp->getPostById($id_post);
                        $dados = $mvp->getDados();
                        if ($dados->id != $post->id_usuario) {
                            $mvp->newNotification($post->id_usuario, $dados->nome . ' comentou em post', '/post/' . $id_post, $dados->foto);
                        }
                        $arr = [
                            'status' => true,
                        ];
                    } else {
                        $arr = [
                            'status' => false,
                        ];
                    }
                } else {
                    $arr = [
                        'status' => false,
                    ];
                }
            } else {
                $arr = [
                    'status' => false,
                ];
            }
        } else {
            $arr = [
                'status' => false,
            ];
        }
        return $arr;
    }
    protected function getPosts()
    {
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked,(SELECT CONCAT( '[', GROUP_CONCAT(JSON_OBJECT('nome', usr.usuario, 'msg', post_comments.message)), ']') from post_comments INNER JOIN usuarios usr ON usr.id = post_comments.id_user WHERE id_post=p.id) as comments
        FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id
            ", [
            Start::session('id_user'),
        ]);
        if ($qr->count() > 0) {
            $posts = $qr->generico()->fetchAll(PDO::FETCH_ASSOC);
            foreach ($posts as $index => $post) {
                ob_start();
                require '../app/view/insta/logado/post.phtml';
                $html_post = ob_get_contents();
                ob_end_clean();
                $posts[$index]['html'] = $html_post;
            }
            return $posts;
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
            return [
                'status' => true,
                'msg' => 'Logado com sucesso',
            ];
        } else {
            return [
                'status' => false,
                'msg' => 'Erro ao logar',
            ];
        }
    }
    public function followUser()
    {
        $gram = new MVPGram;
        if (isset($_POST['id_user'])) {
            return $gram->followUser(Start::post('id_user'));
        } else {
            return ['status' => false, 'msg' => 'Nenhum id de usuário recebido'];
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
                return [
                    'status' => true,
                    'msg' => 'Cadastrado com sucesso',
                ];
            } else {
                return [
                    'status' => false,
                    'msg' => 'Erro ao cadastrar-se',
                ];
            }
        }
    }
}
