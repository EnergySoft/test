<?php 

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
    INNER JOIN core_users AS us ON us.id = comp.user_id AND us.balance > 0 
    LEFT JOIN companies_to_countries AS c_to_countries 
    ON c_to_countries.country_id = ".$country_id." AND c_to_countries.company_id = comp.id AND c_to_countries.status = 1 
    LEFT JOIN companies_to_regions AS c_to_regions 
    ON c_to_regions.region_id = ".$region_id." AND c_to_regions.company_id = comp.id AND c_to_regions.status = 1 
    LEFT JOIN companies_to_cityes AS c_to_cityes 
    ON c_to_cityes.city_id = ".$city_id." AND c_to_cityes.company_id = comp.id AND c_to_cityes.status = 1 
    LEFT JOIN companies_to_objects AS c_to_objects 
    ON c_to_objects.object_id = ".$object_id." AND c_to_objects.company_id = comp.id AND c_to_objects.status = 1 
    WHERE comp.status = 1 
    ORDER BY 1 DESC     

";

//echo $query;

$result = DataBase::query($query);

if($result && pg_num_rows($result) > 0){

    $top_price = false;

    $bidders = Array();

    while($bid = pg_fetch_assoc($result)){

        if(!$top_price){
            $top_price = $bid['price'];
        }

        if($bid['price'] == $top_price){
            $bidders[] = $bid;
        }

    }


    $bidders_amount = count($bidders);
    $winner_bid_index = rand(0,$bidders_amount-1);

    $winner_bid = $bidders[$winner_bid_index];    

    if($winner_bid){

        $unic = time().rand(1000000,9999999);
        $unic = substr(md5($unic),0,29);

        $_response['unic'] = $unic;

        add_impression($DEVICE_ID,$winner_bid['id'], $winner_bid['price'],$unic);

    }

    //var_dump($bidders);

    //var_dump($winner_bid);


} else {

    add_impression($DEVICE_ID,0,0);

}