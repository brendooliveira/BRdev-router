<?php

class Web
{
    public function home()
    {
        echo "Pagina Home";
    }

    public function about()
    {
        echo "Pagina Sobre";
    }

    public function user($data)
    {
        var_dump($data);
    }
}