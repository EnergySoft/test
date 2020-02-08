<?php

    class HtmlLogger{

        public $log_on = true;

        public $log_path = '';
        public $log_name = 'log_';
        public $file_name = '';

        private $old_timestamp = 0;
        private $old_timestamps = Array();

        private $groups = Array();
        private $buffer_output = false;
        private $buffer = '';

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

        //////////////////////////////////////////////// LABEL

        public function label($text, $color = '#000', $background = '#EEE'){
            $html = "<div class='block label' style='color:".$color.";background:".$background."'>";
            $html .= $text;
            $html .= "</div>";
            $this->print($html);
        }

        //////////////////////////////////////////////// DUMPS

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


        public function dump_object($data, $extended = false){


        }
        
        public function dump_table($data, $headers = false){

            //For object or array of objects

            if(!is_array($data)){
                $this->dump_table($data);
                return 0;
            }

            if(!is_array($data[0]) && !is_object($data[0])){
                $this->dump_simple_array($data);
                return 0;
            }

            if(!$headers){
                for($i = 0; $i< count($data); $i++){
                    $obj = $data[$i];
                    foreach($obj as $key=>$value){
                        if(!in_array($key,$headers)){
                            $headers[] = $key;
                        }
                    }
                }
            }

            $html = "<table>";

            $html.="<thead><tr>";
            foreach($headers as $h){
                $html.="<th>".$h."</th>";
            }
            $html.="</tr></thead>";

            $html.="<tbody>";

            for($i = 0; $i< count($data); $i++){
                $html.="<tr>";
                foreach($headers as $h){
                    $html.="<td>";
                    if(isset($data[$i][$h])){
                        $d = $data[$i][$h];
                        if(!is_object($d) && !is_array($d)){
                            $html.=$d;
                        } else {
                            if(is_object($d)){
                                
                            }
                            if(is_array($d)){
                                $this->buffer_output = true;
                                $this->dump_simple_array($d);
                                $html.=$this->buffer;
                            }
                        }
                    }
                    $html.="</td>";
                }
                $html.="</tr>";
            }

            $html.="</tbody>";

            $html.="</table>";

            $this->print($html);
            
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

        /////////////////////////////////////////////// TIMESTAMP

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

        /////////////////////////////////////////////// GROUPS

        public function open_group($name, $timestamp = false){

            $this->groups[] = $name;
            if($timestamp){
                $this->log_timestamp('Group '.$name.' start','group_'.$name);
            }
            $html = "<div class='block group'>
                            <div class='group_header'>
                                <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                                <i class='fa fa-minus-square-o' aria-hidden='true'></i>
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

        /////////////////////////////////////////////// PRIVATE

        private function get_table_html($object){

        }

        private function microtime_float()
        {
            return round(microtime(true) * 1000);
        }

        private function print($text){
            if($this->log_on){
                if(!$this->buffer_output){
                    echo $text;
                    file_put_contents($this->log_path.'/'.$this->file_name, $text, FILE_APPEND);
                } else {
                    $this->buffer = $text;
                    $this->buffer_output = false;
                }
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
                        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
                    </head>
                    <body>

                    <script>
                        $(document).ready(function() {
                            $('.group_header').click(function(){
                                $(this).parent().find('.group_content').first().toggle();
                                $(this).toggleClass('extended');
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
                            padding-left:20px;
                        }

                        .group_header{
                            cursor:pointer;
                            padding-left:3px;
                            user-select:none;
                        }

                        .group_header>.fa-minus-square-o{
                            display:none;
                        }

                        .group_header.extended>.fa-minus-square-o{
                            display:inline;
                        }

                        .group_header.extended>.fa-plus-square-o{
                            display:none;
                        }

                        .group{
                            border-left:2px solid ".$border_color.";
                        }

                        .dump_object{
                            padding:5px;
                            margin:3px;
                            border:1px solid ".$border_color.";
                            border-radius:5px;
                        }

                        .timestamp{
                            font-style:italic;
                        }

                        .scrollblock{
                            max-width:100%;
                            overflow:hidden;
                            overflow-x:auto;
                        }

                        .label{
                            border-radius:4px;
                            padding:3px;
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
    $test3 = Array(
        Array('type'=>'cat', 'gender'=>'male','name'=>'Ricky'),
        Array('type'=>'cat', 'gender'=>'male','name'=>'Bobby','owners'=>Array('Sam','Lucy','Vahtang')),
        Array('type'=>'dog', 'gender'=>'female','name'=>'Lucky'),
        Array('type'=>'dog', 'gender'=>'male','name'=>'Demon','cubs'=>6),
    );


    $logger = new HtmlLogger;

    $logger->log_path = '/home/vladimir/websites/test';
    $logger->log_name = 'testlog';
    $logger->create_log_file();

    $logger->open_group('LOG', true);
    $logger->label('TEST 2','#FFF','#a60000');
    $logger->dump_simple_array($test2, true, false);
    $logger->label('TEST 3','#FFF','#0e00d6');
    $logger->dump_table($test3, false);
    $logger->close_group(true);

    file_get_contents();

    