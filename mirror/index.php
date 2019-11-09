<?php

    header('Access-Control-Allow-Origin: *');        
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');//
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Referer, User-Agent');//
    header('Access-Control-Allow-Credentials: true');

    DEFINE('DATE_FORMAT','YYYY-MM-DD"T"HH24:MI:SS"Z"');
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }


    //Default response object
    $_response = Array(
        'error_code' => 0,
        'error' => '',
        'result' => Array()
    );

    $_params = $_REQUEST; //for test
    

    /*
    $postBody = file_get_contents('php://input');

    if(trim($postBody)){
        $_params = json_decode($postBody,true);
    }
    */

    $action = $_params['action'];

    $DEVICE_ID = intval($_params['device_id']);
    
    include './classes/config.php';
    include './classes/database.php';
    include './config.php';
    include './functions.php';

    $device = DataBase::query("SELECT * FROM devices WHERE id = ".$DEVICE_ID, true);
  
    if($device['status'] != 1){
        exit;
    }    

    if($action == 'get_ad'){
        include './actions/get_ad.php';        
    }

    if($action == 'register_impression'){
        include './actions/register_impression.php';        
    }

    echo(json_encode($_response));

    
