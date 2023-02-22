<?php
declare(strict_types = 1);

use Core\ClassLoader;
use Core\Database;
use Core\Router;

// Configuration file
require_once __DIR__ . '/App/config.php';

// include all functions
foreach (glob(__DIR__ . '/Core/functions/*.php') as $file) {
    require_once $file;
}

// include Class Auto loader
require_once __DIR__ . '/Core/ClassLoader.php';
ClassLoader::is_strip_namespace(true);
ClassLoader::add_paths([
    __DIR__. '/Core/',
    __DIR__. '/App/Controlers'
]);
ClassLoader::init();

// set Database configs
foreach ($dbconf as $key => $val) {
    Database::setConfig($key, $val);
} 



$router = new Router;

$router->get('/', \Controler\HomeControler::class);
$router->get('/home', \Controler\HomeControler::class);


// test 
$router->get('/test', function() {
    return 'Hello World';
});


// error handles
$router->get('/error/404', \Controler\ErrorPageControler::class);
$router->get('/error/noscript', \Controler\ErrorPageControler::class, 'noscript');



// $router->get('/user/{int:id}', function($params) {
//     return 'User view page ; id =' . $params['id'];
// });


$router->default(\Controler\ErrorPageControler::class, 'main');

$router->run();


