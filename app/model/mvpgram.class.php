<?php
    namespace Insta;
    use JetPHP\Model\Criptography;
    use JetPHP\Model\DB;
    use JetPHP\Model\Start;
    use JetPHP\Model\Upload;

    class MVPGram {
        private $dadosUsuario;

        public function __construct() {
            $u = new MVPUtils();
            if ($u->isOnline()) {
                $idUsuario = Start::session('id_user');
                $this->dadosUsuario = $u->getData($idUsuario);
            }
        }

        public function listarPosts() {
            $idUsuario = Start::session('id_user');
            $sql = "SELECT * FROM posts p 
                LEFT JOIN usuarios_seguir s ON (s.id_seguido = p.id_usuario)
                WHERE s.id_usuario = ? or p.id_usuario = ?";
            $qr = DB::execute($sql, [
                    $idUsuario,$idUsuario
            ]);
            if ($qr->count() > 0) {
                return $qr->generico()->fetchAll(\PDO::FETCH_OBJ);
            } else {
                return false;
            }
        }

        public function getDadosUsuario() {
            return $this->dadosUsuario;
        }

        public function enviarPost() {
            $imagem = $_FILES['imagem'];
            $descricao = Start::post('descricao');
            $idUsuario = Start::session('id_user');

            $upload = new Upload($imagem);
            $upload->addExtensions([
                'jpg', 'jpeg', 'png'
            ]);
            $upload->setFilepath('upload/posts');
            $upload->setFilename(Criptography::md5(time().$this->dadosUsuario->id));
            $s = $upload->sendFile();

            if ($s['status'] == 'success') {
                $nomeImg = $s['name'];
                $qr = DB::execute("INSERT INTO posts (id_usuario, descricao, imagem) VALUES (?,?,?)",[
                    $idUsuario,
                    $descricao,
                    $nomeImg
                ]);
                if ($qr->count() > 0) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }