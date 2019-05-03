<?php
    use Insta\MVPUtils;
    if (isset($_POST['user']) and isset($_POST['pass'])) {
        $util = new MVPUtils();
        $login = $util->login();
        $msg = 'Logado com sucesso';
        if (!$login) $msg = 'Usuário e/ou senha incorretos';

        echo json_encode([
            'status'=>$login,
            'msg' => $msg
        ]);
    } else {
        echo json_encode([
            'status'=>false,
            'msg' => 'Nenhum dado recebido via POST'
        ]);
    }