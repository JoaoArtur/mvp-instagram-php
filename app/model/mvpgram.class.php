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
        WHERE u.usuario = ?", [$user]);
        if ($qr->count() > 0) {
            return $qr->list(PDO::FETCH_OBJ);
        } else {
            return false;
        }
    }
    public function getDadosByUid($user)
    {
        $qr = DB::execute("SELECT u.id,u.onesignal_id, u.nome, u.email, u.usuario, u.telefone, u.site, u.biografia,u.sexo,u.foto,GROUP_CONCAT(p.id) as posts FROM usuarios u
        LEFT JOIN posts p ON p.id_usuario=u.id
        WHERE u.id = ?", [$user]);
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
    public function getPostById($id)
    {
        $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario,pl.id as liked
        FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            LEFT JOIN post_likes pl ON pl.id_user = ? and pl.id_post = p.id
        WHERE p.id = ?", [
            Start::session('id_user'),
            $id,
        ]);
        if ($qr->count() > 0) {
            return $qr->list(PDO::FETCH_OBJ);
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
    public function sendOnesignalNotification($id_user, $texto) {
        $dados = $this->getDadosByUid($id_user);
        $onesignal_id = $dados->onesignal_id;
	$appId = 'SEU APP_ID';
	$authorization = 'Sua Authorization';
        if ($onesignal_id != '') {
            $content      = array(
                "en" => $texto,
                "pt" => $texto
            );
            $fields = array(
                'app_id' => $appId,
                'include_player_ids' => [$onesignal_id],
                'data' => array(
                    "foo" => "bar"
                ),
                'contents' => $content,
            );
            $fields = json_encode($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic '.$authorization
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } else {
            return false;
        }
    }
    public function newNotification($id_user, $texto, $link, $img)
    {
        $qr = DB::getInstance()->execute("INSERT INTO usuarios_notificacao (id_user,texto,link,img) VALUES (?,?,?,?)", [
            $id_user, $texto, $link, $img
        ]);
        if ($qr->count() > 0) {
            $this->sendOnesignalNotification($id_user, $texto);
            return true;
        } else {
            return false;
        }
    }
    public function getNotifications()
    {
        $qr = DB::getInstance()->execute("SELECT * FROM usuarios_notificacao WHERE id_user = ? ORDER BY id DESC", [
            Start::session('id_user')
        ]);
        if ($qr->count() > 0) {
            return $qr->generico()->fetchAll(PDO::FETCH_OBJ);
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
                $dados = $this->getDados();
                $this->newNotification($id_user, $dados->nome . ' seguiu você', '/' . $dados->usuario, $dados->foto);
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
