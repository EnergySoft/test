<?php

    class HtmlLogger{

        public $log_on = true;

        public $log_path = '';
        public $log_name = 'log_';
        public $file_name = '';

        private $old_timestamp = 0;
        private $old_timestamps = Array();

        private $groups = Array();

        public function create_new_log_file(){
            $this->file_name = $this->log_name.'_'.date('Y-m-d_H-i-s').'.html';
            $this->print($this->get_file_header());
        }

        public function create_log_file(){
            $this->file_name = $this->log_name.'.html';
            if(file_exists($this->log_path.'/'.$this->file_name)){
                unlink($this->log_path.'/'.$this->file_name);
            }
            $this->print($this->get_file_header());
        }

        public function dump($data, $pre = true){

            ob_start();
            var_dump($data);
            $result = ob_get_clean();

            $html = "<div class='block dump_object'>";
            if($pre) $html.="<pre>";
            $html .= $result;
            if($pre) $html.="</pre>";
            $hmtml.= "</div>";
            $this->print($html);
        }
        
        public function log_timestamp($comment = '',$name = ''){

            $time_text = date('Y-m-d H:i:s');
            $timestamp = $this->microtime_float();
            $time_diff = 0;

            if($name == ''){

                if($this->old_timestamp == 0){
                    $this->old_timestamp = $timestamp;
                }
                $time_diff = $timestamp - $this->old_timestamp;
                $this->old_timestamp = $timestamp;

            } else {

                if(!isset($this->old_timestamps[$name])){
                    $this->old_timestamps[$name] = $timestamp;
                }
                $time_diff = $timestamp - $this->old_timestamps[$name];
                $this->old_timestamps[$name] = $timestamp;

            }

            $html = "
            <div class='block timestamp'>
                <span class='timestamp_time'>[".$time_text."] [".$timestamp."] [".$time_diff."]</span>
                <span class='timestamp_comment'>".$comment."</span>
            </div>";

            $this->print($html);

        }

        public function open_group($name, $timestamp = false){

            $this->groups[] = $name;
            if($timestamp){
                $this->log_timestamp('Group '.$name.' start','group_'.$name);
            }
            $html = "<div class='block group'>
                            <div class='group_header'>
                                <span class='group_name'>GROUP: <b>".$name."</b></span>
                            </div>
                            <div class='group_content'>";
            $this->print($html);

        }

        public function close_group($timestamp = false){

            $name = array_pop($this->groups);
            $html = "</div></div></div>";
            $this->print($html);
            if($timestamp){
                $this->log_timestamp('Group '.$name.' end','group_'.$name);
            }
            
        }

        public function dump_table($data, $headers = false){

            //For object or array of objects

            if(!$headers){

                $obj = false;

                if(is_array($data)){
                    if(is_object)
                    $obj = $data;
                }

                if(is_object($data)){
                    $obj = $data;
                }

            }
            
        }

        public function dump_simple_array($array, $detailed = true, $pre = false){

            $row1 = "";
            $row2 = "";

            foreach($array as $key=>$value){
                $row1.="<th>".$key."</th>";

                $row2.="<td>";

                if($detailed && (is_array($value) || is_object($value))){

                    

                    ob_start();
                    var_dump($value);
                    $result = ob_get_clean();
        
                    if($pre) $row2.="<pre>";
                    $row2 .= $result;
                    if($pre) $row2.="</pre>";


                } else {

                    $row2.=$value;
                    
                }

                $row2.="</td>";
            }

            $html = "<table>";

            $html .= "<thead><tr>";
            $html .= $row1;
            $html .= "</tr></thead>";

            $html .= "<tbody><tr>";
            $html .= $row2;
            $html .= "</tr></tbody>";

            $html .= "</table>";

            $html = "<div class='block scrollblock'>".$html."</div>";

            $this->print($html);

        }

        /////////////////////////////////////////////// PRIVATE

        private function get_table_html($object){

        }

        private function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }

        private function print($text){
            if($this->log_on){
                echo $text;
                file_put_contents($this->log_path.'/'.$this->file_name, $text, FILE_APPEND);
            }
        }

        private function get_file_header(){

            $text_color = '#000';
            $border_color = '#DDD';
            $border_table_color = '#444';

            $html = "
                <html>
                    <head> 
                    <script
                        src='https://code.jquery.com/jquery-3.4.1.min.js' 
                        integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo='
                        crossorigin='anonymous'></script>
                        <link rel='style' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
                    </head>
                    <body>

                    <script>
                        $(document).ready(function() {
                            $('.group_header').click(function(){
                                $(this).parent().find('.group_content').first().toggle();
                            });
                        });
                    </script>

                    <style>

                        body{
                            padding:10px;
                            margin:0px;
                            font-family: Arial;
                            font-size:14px;
                            color:".$text_color.";
                        }

                        .block{
                            margin-top:2px;
                            margin-bottom:2px;
                        }

                        table {
                            border-collapse: collapse;
                        }

                        thead{
                            background:#EEE;
                        }
                          
                        table, th, td {
                            border: 1px solid ".$border_table_color.";
                        }

                        .group_content{
                            display:none;
                            padding-left:15px;
                        }

                        .dump_object{
                            padding:5px;
                            margin:3px;
                            border:1px solid ".$border_color.";
                            border-radius:5px;
                        }

                        .group_header{
                            cursor:pointer;
                        }

                        .group{
                            border-left:2px solid ".$border_color.";
                        }

                        .timestamp{
                            font-style:italic;
                        }

                        .scrollblock{
                            max-width:90%;
                            overflow:hidden;
                            overflow-x:scroll;
                        }

                    </style>

            ";

            return $html;

        }

        private function get_file_footer(){
            $html = "
                </body>
                </html>
            ";
            return $html;
        }

    }



    $data1 = Array(
        


    );

    $test1 = "1213";
    $test2 = Array(1,2,43,32423,6623,43,32423,6623,1,2,43,32423,6623,43,32423,34, 34,345, 567,234, 233, 23423, 34,236623,43,32423,6623,Array(33,33,33));

    $logger = new HtmlLogger;

    $logger->log_path = '/home/vladimir/websites/test';
    $logger->log_name = 'testlog';
    $logger->create_log_file();

    $logger->open_group('test');
    $logger->dump_simple_array($test2, true, false);
    $logger->close_group();

    file_get_contents();

    