<?php

use BRdev\Router\Router;

require __DIR__."/../vendor/autoload.php";

require 'Web.php';
 

Router::get('/','Web@home');
Router::get('/sobre','Web@about');
Router::get('/user/{id}', 'Web@user');


Router::dispatch();