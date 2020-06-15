<?php
    use JetPHP\Model\JetLoad;
    class MVPGramController extends MVPGram {
        public function __construct() {
            parent::__construct();
        }

        private static function load($view, $vars=null) {
            $jt = new JetLoad();
            $jt->setTop('insta.logado.inc.top');
            $jt->setMenu('insta.logado.inc.menu');
            $jt->setFooter('insta.logado.inc.footer');
            $jt->addVars($vars);
            $jt->view($view);
        }

        public static function dashboard() {
            return self::load('insta.logado.dash', ['mvp'=> new MVPGram, 'not'=>self::getNotifications(),'dadosUsuario' => self::$dadosUsuario, 'posts' => self::getPosts()]);
        }
    }