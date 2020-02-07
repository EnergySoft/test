<?php 

function add_impression($device, $company, $object, $city, $region, $country, $price, $unic = ''){

    $query = "
        INSERT INTO impressions 
        (
            date_impression,
            device_id,
            company_id,
            object_id,
            city_id,
            region_id,
            country_id,
            price,
            status,
            unic
        )
        VALUES
        (
            CURRENT_TIMESTAMP,
            ".$device.",
            ".$company.",
            ".$object.",
            ".$city.",
            ".$region.",
            ".$country.",
            ".$price.",
            0,
            '".$unic."'
        )
    ";

    DataBase::query($query);

}

function sign($text){

    $salt = Config::get('salt');
    return md5(md5($salt.$text).$salt);

}