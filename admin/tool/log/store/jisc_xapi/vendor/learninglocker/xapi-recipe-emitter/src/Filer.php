<?php namespace XREmitter;
use \TinCan\RemoteLRS as TinCanRemoteLrs;
use \TinCan\Statement as TinCanStatement;
use \stdClass as PhpObj;

class Filer extends PhpObj {
    protected $store;

    public function getGUID(){


    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));


    }
    /**
     * Constructs a new Repository.
     * @param TinCanRemoteLrs $store
     * @param PhpObj $cfg
     */
    public function __construct() {

    }

    /**
     * Creates an event in the store.
     * @param [string => mixed] $statements
     * @return [string => mixed]
     */
    public function createEvents(array $statements) {
       // $this->store->saveStatements($statements);

      //  echo (print_r($statements));


      /*  $gui = $this->getGUID();

        $event->id =  $gui ;

        echo 'id is '.$gui;

        $event['id']=$gui;*/

        $txt= json_encode($statements[0], JSON_UNESCAPED_SLASHES);

     //   echo 'id is '. $event['id'];

      //  $file = 'o.txt';

        $file  = $_GET["path"];


        file_put_contents($file, $txt, FILE_APPEND | LOCK_EX);

        file_put_contents($file, "\n", FILE_APPEND | LOCK_EX);

        echo('done statement');
        return $statements;
    }
}
