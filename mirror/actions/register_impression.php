<?php 

    $unic = $_params['unic'];
    $sing = $_params['sign'];

    if(sign($unic) == $sign || true){

        $impression = DataBase::query("SELECT * FROM impressions WHERE unic = '".$unic."'", true);

        if($impression && $impression['status'] == 0){

            $object_id = $impression['object_id'];
            $price = $impression['price'];
            $price_clear = $price;

            $partners = DataBase::query("SELECT * FROM partners_to_objects WHERE object_id = ".$object_id);            
            
            while($partner = pg_fetch_assoc($partners)){

                $reward = round($price*($partner['percent']/100),2);
                $price_clear -= $reward;

                $query = "
                    INSERT INTO 
                    (
                        date_impression,
                        partner_id,
                        object_id,
                        reward
                    ) VALUES (
                        CURRENT_TIMESTAMP,
                        ".$partner['user_id'].",
                        ".$object_id.",
                        ".$reward."
                    )
                ";

                DataBase::query($query);

            }
        
            DataBase::query("
                UPDATE impressions 
                SET status = 1,
                price_clear = ".$price_clear."
                WHERE unic = '".$unic."' 
            ");



        }

    } else {

        $_response['error_code'] = '13';

    }

