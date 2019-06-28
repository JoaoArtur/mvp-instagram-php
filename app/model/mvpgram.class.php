<?php
    use JetPHP\Model\DB;
    use JetPHP\Model\Start;
    class MVPGram {
        protected static $dadosUsuario;

        public function __construct() {
            self::$dadosUsuario = $this->getDados();
        }

        private function getDados() {
            $qr = DB::execute("SELECT * FROM usuarios WHERE id = ?", [Start::session('id_user')]);
            if ($qr->count() > 0) {
                return $qr->list(PDO::FETCH_OBJ);
            } else {
                return false;
            }
        }

        public function getPosts() {
            $qr = DB::execute("SELECT p.*, u.nome, u.usuario, u.foto as imagemUsuario FROM posts as p
            INNER JOIN usuarios u ON u.id = p.id_usuario");
            if ($qr->count() > 0) {
                return $qr->generico()->fetchAll(PDO::FETCH_OBJ);
            } else {
                return false;
            }

        }
    }