<?php

use BRdev\Router\Router;

require __DIR__."/../vendor/autoload.php";

//namespace
Router::namespace("BRdev\Router\Web");
Router::get("/","Web@home");
Router::get("/sobre","Web@about");

Router::post("/post", function ($data) {
    var_dump($data);
});

//namespace
Router::namespace("BRdev\Router\App");
Router::get("/user/{id}", "App@user");

Router::get("/error/{code}", function($data) {
    var_dump($data["code"]);
});

Router::dispatch();

if(Router::getError()){
    Router::redirect("/error/".Router::getError());
}