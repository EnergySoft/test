<?php 

class DataBase {

    public static $_connected = false;
    public static $_conn = null;
    private static $_current_str = '';

    public static function connect($str = ''){

        if($str == ''){
            $str = Config::get('conn_str');
        }

        if($str != self::$_current_str){
            if(self::$_conn){
                pg_close(self::$_conn);
                self::$_conn = null;
            }
        }

        if(!self::$_conn){
            self::$_conn = pg_connect($str);
            self::$_current_str = $str;
        }

    }

    //$_queries_array = Array();

    public static function query($query, $fetch = false){
        
        if(!self::$_conn){
            self::connect();
        }
        
        if(self::$_conn){
            //echo "<br><br><br>". $query."<br><br><br>";
            $res = pg_query(self::$_conn, $query);
            if(!$fetch){
                return $res;
            } else {
                if(!$res) return false;
                return pg_fetch_assoc($res);
            }
        } else {
            echo json_encode(Array('result'=>'db connection error'));
            exit;
        }

    }

    public static function close_connection(){
        pg_close(self::$_conn);
        self::$_conn = null;
    }

}