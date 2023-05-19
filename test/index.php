<?php

use BRdev\Router\Router;

require __DIR__."/../vendor/autoload.php";

//namespace
Router::namespace("BRdev\Router\Web");
Router::get('/','Web@home');
Router::get('/sobre','Web@about');

//namespace
Router::namespace("BRdev\Router\App");
//Router::get('/user/{id}', 'App@user');

Router::group('/admin');
    Router::get('/user/{id}', 'App@user');
Router::endgroup();

Router::group('/error');
    Router::get('/{code}', function($data) {
        var_dump($data->code);
    });
Router::endgroup();


Router::dispatch();     

if(Router::error()){
    Router::redirect("/error/".Router::geterror());
}