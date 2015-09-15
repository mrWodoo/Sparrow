<?php
define( 'DEV_MODE', true );

return array (
  'database' => [
      'host' => 'localhost',
      'name' => 'sparrow',
      'user' => 'root',
      'password' => 'a',
      'prefix' => 'sp_',
      'port' => 3306,
      ],
  'cache' => [
      'storage' => './Storage/',
      'cache' => './Storage/Cache/'
  ],
  'board' => [
      'domain' => 'localhost',
      'path' => '/Sparrow/',
      'title' => 'Test board',
      'template' => 'bootstrap',
      'domain' => 'localhost',
      'path' => '/Sparrow/'
  ],
);
?>