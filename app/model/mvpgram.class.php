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

    private function getDados()
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
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id
        WHERE p.id_usuario = ?", [
            Start::session('id_user'),
            $uid
        ]);
        if ($qr->count() > 0) {
            return $qr->generico()->fetchAll(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }
    public function getPosts()
    {
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id", [
            Start::session('id_user')
        ]);
        if ($qr->count() > 0) {
            return $qr->generico()->fetchAll(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }
}
