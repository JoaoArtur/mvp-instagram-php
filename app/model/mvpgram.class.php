<?php
use JetPHP\Model\DB;
use JetPHP\Model\Start;

class MVPGram
{
    protected static $dadosUsuario;

    public function __construct()
    {
        self::$dadosUsuario = $this->getDados();
    }

    public function getDados()
    {
        $qr = DB::execute("SELECT * FROM usuarios WHERE id = ?", [Start::session('id_user')]);
        if ($qr->count() > 0) {
            return $qr->list(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }

    public function getDadosByUser($user)
    {
        $qr = DB::execute("SELECT u.id, u.nome, u.email, u.usuario, u.telefone, u.site, u.biografia,u.sexo,u.foto,GROUP_CONCAT(p.id) as posts FROM usuarios u
        LEFT JOIN posts p ON p.id_usuario=u.id
        WHERE usuario = ?", [$user]);
        if ($qr->count() > 0) {
            return $qr->list(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }
    public function getPostsByUid($uid)
    {
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked
        FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id
        WHERE p.id_usuario = ?", [
            Start::session('id_user'),
            $uid,
        ]);
        if ($qr->count() > 0) {
            return $qr->generico()->fetchAll(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }
    public function getPosts()
    {
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked,
        (SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('nome',usr.usuario,'msg',post_comments.message)), ']')
        FROM post_comments INNER JOIN usuarios usr ON usr.id = post_comments.id_user WHERE id_post=p.id) as comments
        FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id", [
            Start::session('id_user'),
        ]);
        if ($qr->count() > 0) {
            $arr = $qr->generico()->fetchAll(PDO::FETCH_OBJ);
            return $arr;
        } else {
            return false;
        }
    }
    public function checkFollow($id_user)
    {
        $qr = DB::getInstance()->execute("SELECT * FROM usuarios_seguir WHERE id_usuario = ? and id_seguido = ?", [
            Start::session('id_user'),
            $id_user,
        ]);
        if ($qr->count() > 0) {
            return true;
        } else {
            return false;
        }

    }
    public function followUser($id_user)
    {
        if ($this->checkFollow($id_user)) {
            $qr = DB::getInstance()->execute("DELETE FROM usuarios_seguir WHERE id_usuario = ? and id_seguido = ?", [
                Start::session('id_user'),
                $id_user,
            ]);
            if ($qr->count() > 0) {
                $arr = [
                    'status' => true,
                    'msg' => 'unfollowed',
                    'txt' => 'Seguir',
                ];
            } else {
                $arr = [
                    'status' => false,
                ];
            }
        } else {
            $qr = DB::getInstance()->execute("INSERT INTO usuarios_seguir (id_usuario,id_seguido) VALUES (?,?)", [
                Start::session('id_user'),
                $id_user,
            ]);
            if ($qr->count() > 0) {
                $arr = [
                    'status' => true,
                    'msg' => 'followed',
                    'txt' => 'Deixar de seguir',
                ];
            } else {
                $arr = [
                    'status' => false,
                ];
            }
        }
        return $arr;
    }
}
