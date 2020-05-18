<?php
use Insta\MVPUtils;
if (isset($_POST['pass'])) {
    $util = new MVPUtils();
    $login = $util->register();
    $msg = 'Registrado com sucesso';
    if (!$login) $msg = 'Erro ao criar usuário';

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