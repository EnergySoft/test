<?php 

function add_impression($device,$company,$price, $unic = ''){

    $query = "
        INSERT INTO impression 
        (
            date_impression,
            device_id,
            company_id,
            price,
            status,
            unic
        )
        VALUES
        (
            CURRENT_TIMESTAMP,
            ".$device.",
            ".$company.",
            ".$price.",
            '".$unic."'
        )
    ";

    DataBase::query($query);

}

function sign($text){

    $salt = Config::get('salt');
    return md5(md5($salt.$text).$salt);

}