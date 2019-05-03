<?php
  class Controle {
    public static function view($caminho,$var=null,$admin=false,$menu=true) {
      \JetPHP\Model\Load::view($caminho,$var,$admin,$menu);
    }
  }
?>
