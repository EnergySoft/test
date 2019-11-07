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
    $action = 'get_list';

    /*
    $postBody = file_get_contents('php://input');

    if(trim($postBody)){
        $_params = json_decode($postBody,true);
    }
    */

    $DEVICE_ID = intval($_params['device_id']);
    
    include './classes/config.php';
    include './classes/database.php';
    include './config.php';

    $device = DataBase::query("SELECT * FROM devices WHERE id = ".$DEVICE_ID, true);
  

    if($device['status'] != 1){
        exit;
    }    
    
    $object = DataBase::query("SELECT * FROM objects WHERE id = ".$device['object_id'], true);
    $city = DataBase::query("SELECT * FROM cityes WHERE id = ".$object['city_id'], true);
    $region = DataBase::query("SELECT * FROM regions WHERE id = ".$city['region_id'], true);
    $country = DataBase::query("SELECT * FROM countries WHERE id = ".$region['country_id'], true);
    
    
    

    $country_id = $country['id'];
    $region_id = $region['id'];
    $city_id = $city['id'];
    $object_id = $object['id'];

    $query = "
    
        SELECT 
        COALESCE(c_to_countries.price, c_to_regions.price, c_to_cityes.price, c_to_objects.price) as price,
        comp.id
         
        FROM companies as comp 

        INNER JOIN core_users AS user ON user.id - comp.user_id AND user.balance > 0 

        LEFT JOIN companies_to_countries AS c_to_countries 
        ON c_to_countries.country_id = ".$country_id." AND c_to_countries.company_id = comp.id AND c_to_countries.status_real = 1
        LEFT JOIN companies_to_regions AS c_to_regions 
        ON c_to_regions.region_id = ".$region_id." AND c_to_regions.company_id = comp.id AND c_to_regions.status_real = 1
        LEFT JOIN companies_to_cityes AS c_to_cityes 
        ON c_to_cityes.city_id = ".$city_id." AND c_to_cityes.company_id = comp.id AND c_to_cityes.status_real = 1
        LEFT JOIN companies_to_objects AS c_to_objects 
        ON c_to_objects.object_id = ".$object_id." AND c_to_objects.company_id = comp.id AND c_to_objects.status_real = 1 
        WHERE comp.status = 1

        ORDER BY 1 DESC         
    
    ";

    echo $query;

    echo(json_encode($_response));

    
