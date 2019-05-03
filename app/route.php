<?php
  use JetPHP\Model\Route;
  use Insta\MVPUtils;
  $utils = new MVPUtils();

  if ($utils->isOnline()) {
    Route::add('/', 'paginaInicial@MVPGramController');
    Route::add('new-post/', 'novoPost@MVPGramController');




  } else {
    Route::add('/','login@MVPController');
    Route::add('api/:pagina', 'api@MVPController');
    Route::add('accounts/:tipo', 'account@MVPController');
  }
?>
