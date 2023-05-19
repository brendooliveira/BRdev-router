###### Small, simple and uncomplicated. The router is a PHP route components with abstraction for MVC. Prepared with RESTfull verbs (GET, POST, PUT, PATCH and DELETE), works on its own layer in isolation and can be integrated without secrets to your application.

Pequeno, simples e descomplicado. O router é um componentes de rotas PHP com abstração para MVC. Preparado com verbos
RESTfull (GET, POST, PUT, PATCH e DELETE), trabalha em sua própria camada de forma isolada e pode ser integrado sem
segredos a sua aplicação.

## Installation

Router is available via Composer:

```bash
"brdev/router": "1.5"
```

or run

```bash
composer require brdev/router
```

## Documentation

###### For details on how to use the router, see the sample folder with details in the component directory. To use the router you need to redirect your route routing navigation (index.php) where all traffic must be handled. The example below shows how:

Para mais detalhes sobre como usar o router, veja a pasta de exemplo com detalhes no diretório do componente. Para usar
o router é preciso redirecionar sua navegação para o arquivo raiz de rotas (index.php) onde todo o tráfego deve ser
tratado. O exemplo abaixo mostra como:

#### Apache

```apacheconfig
RewriteEngine On
Options All -Indexes


# ROUTER HTTPS Redirect
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# ROUTER URL Rewrite
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=/$1 [L,QSA]
```

#### Nginx

````nginxconfig
location / {
  if ($script_filename !~ "-f"){
    rewrite ^(.*)$ /index.php?route=/$1 break;
  }
}
````

##### Routes

```php

<?php

use BRdev\Router\Router;

require __DIR__."/vendor/autoload.php";
 
//namespace
Router::namespace("BRdev\Router\Web");
Router::get('/','Web@home');
Router::get('/sobre','Web@about');

//namespace
Router::namespace("BRdev\Router\App");
Router::get('/user/{id}', 'App@user');

Router::group('/error');
Router::get('/{code}','App@error');
Router::endgroup();

Router::dispatch();

//error
if(Router::error()){
    Router::redirect("/error/".Router::getError());
}

```

##### Callable

```php

<?php

use BRdev\Router\Router;

require __DIR__."/vendor/autoload.php";
 

Router::get('/', function () {
    echo "Pagina Home";
});

Router::get('/sobre', function () {
    echo "Pagina Sobre";
});

Router::get('/user/{id}', function ($data) {
    echo "User ". $data->id;
});

Router::group('/error');
    Router::get('/{code}',function ($data){
        var_dump($data->code)
    });
Router::endgroup();

Router::dispatch();

//error
if(Router::error()){
    Router::redirect("/error/".Router::getError());
}

```
