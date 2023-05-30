<?php

class Web
{
    /**
     *
     * @return void
     */
    public function home(): void
    {
        echo "Pagina Home";
    }

    /**
     *
     * @return void
     */
    public function about(): void
    {
        echo "Pagina Sobre";
    }

    /**
     *
     * @return void
     */
    public function user($data): void
    {
        var_dump($data);
    }
}
