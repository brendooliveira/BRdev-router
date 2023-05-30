<?php

namespace BRdev\Router\App;
class App
{

   /**
   * @return void
   */
   public function user($data): void
   {
      echo $data->id;
   }

}
