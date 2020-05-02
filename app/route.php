<?php
  use JetPHP\Model\Route;
  use Insta\MVPUtils;
  $utils = new MVPUtils();

  if ($utils->isOnline()) {
    Route::add('/', 'dashboard@MVPGramController');
  } else {
    Route::add('/','login@MVPController');
    Route::add('accounts/:tipo', 'account@MVPController');
  }
  Route::add('api/:p', 'index@APIController');
  
?>
