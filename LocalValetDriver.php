<?php


class LocalValetDriver extends LaravelValetDriver
{
    /**
    * Determine if the driver serves the request.
    *
    * @param  string  $sitePath
    * @param  string  $siteName
    * @param  string  $uri
    * @return bool
    */
    public function serves($sitePath, $siteName, $uri)
    {
        return true;
    }

    /**
    * Get the fully resolved path to the application's front controller.
    *
    * @param  string  $sitePath
    * @param  string  $siteName
    * @param  string  $uri
    * @return string
    */
    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        $_SERVER['VAR_DUMPER_FORMAT'] = 'server';
        //$_SERVER['VAR_DUMPER_SERVER'] = 'tcp://127.0.0.1:9912';
        if(stristr($uri,'/ckeditor/plugins/')){
            return $sitePath.'/public/'.$uri;
        }
        if(stristr($uri,'/ckeditor_png/plugins/')){
            return $sitePath.'/public/'.$uri;
        }
        if(stristr($uri,'/integration/configurationjs.php')){
            return $sitePath.'/public/'.$uri;
        }
        return $sitePath.'/public/index.php';
    }
}