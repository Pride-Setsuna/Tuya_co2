<?php

class Autoloader{
   /**
     * autoload
     * @param string $class
     * @return void
     */
    public static function autoload($class) {
        $name = $class;
        if(false !== strpos($name,'\\')){
          $name = strstr($class, "\\", true);
        }

        $filename =AUTOLOADER_PATH. "/lib/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }
    }
}

spl_autoload_register('Autoloader::autoload');
?>