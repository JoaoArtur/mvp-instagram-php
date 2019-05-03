<?php
    use Insta\MVPGram;
    use JetPHP\Model\JetLoad;
    class MVPGramController extends Controle {
        private static function load($path, $vars=false) {
            $jl = new JetLoad();
            $jl->addVars($vars);
            $jl->setTop('insta.logado.inc.top')
                ->setMenu('insta.logado.inc.menu')
                ->setFooter('insta.logado.inc.footer')->view($path);
        }
        public static function paginaInicial() {
            $core = new MVPGram();
            $dadosUser = $core->getDadosUsuario();
            $posts = $core->listarPosts();

            return self::load('insta.logado.dash',
                [
                    'dadosUsuario' => $dadosUser,
                    'posts' => $posts
                ]);
        }


        public static function novoPost() {
            $core = new MVPGram();
            $dadosUser = $core->getDadosUsuario();
            if (isset($_POST['descricao'])) {
                $post = $core->enviarPost();
                if ($post) {
                    echo "Imagem enviada com sucesso";
                }
            }

            return self::load('insta.logado.newpost');
        }
    }