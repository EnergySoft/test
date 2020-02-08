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

            $html = "<div class='block dump'>";
            if($pre) $html.="<pre>";
            $html .= $result;
            if($pre) $html.="</pre>";
            $hmtml.= "</div>";
            $this->print($html);

        }


        public function dump_object($data, $extended = false){

            $html.="<div class='block object'>";
            $html.="<div class='object_header'>#".get_class($data)."
                <i class='fa fa-plus-square-o' aria-hidden='true'></i>
                <i class='fa fa-minus-square-o' aria-hidden='true'></i>
            </div>";
            $html.="<div class='block object_content'>";

            if(is_object($data)){

                $html.="<table class='table_object'>";
                foreach($data as $key => $value){
                    $html.="<tr>";
                    $html.="<td class='noborder'>".$key.":</td>";
                    $html.="<td  class='noborder'>";
                    $html.=$this->write_var($value);
                    $html.="</td>";
                    $html.="</tr>";
                }
                $html.="</table>";

            }

            $html.="</div>";
            $html.="</div>";

            $this->print($html);

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
                        $html.= $this->write_var($d);
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

                $row2.=$this->write_var($value);

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

            $html = "<div class='scrollblock'>".$html."</div>";

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
                <i class='fa fa-clock-o'></i>
                <span class='timestamp_time'>[".$time_text."] [".$this->write_ms($time_diff)."]</span>
                <span class='timestamp_comment'><b>".$comment."</b></span>
            </div>
            ";

            $this->print($html);

        }

        /////////////////////////////////////////////// ERRORS

        public function print_error($text = 'error', $code = ''){

            if($code!=''){
                $code = '<b>#'.$code.':</b> ';
            }

            $this->print_block('error','fa fa-exclamation-circle' ,$code.$text);

        }

        public function print_warning($text = 'warning'){

            $this->print_block('warning','fa fa-exclamation-circle', $text);

        }

        public function print_info($text = 'info'){

            $this->print_block('info','fa fa-info-circle',$text);
            
        }

        private function print_block($type,$icon,$text){

            $html = "<div class='block infoblock block-".$type."'>";
            $html.= "<div><i class='".$icon."'></i></div>";
            $html.= $text;
            $html.= "</div>";
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

        /////////////////////////////////////////////// WRITERS

        private function write_ms($ms){
            if($ms < 1000) return $ms.' ms';
            if($ms > 1000) return round($ms/1000,3).' s';
        }

        private function write_var($var){

            if(is_object($var)){
                $this->buffer_output = true;
                $this->dump_object($var);
                return $this->buffer;
            }

            if(is_array($var)){
                $this->buffer_output = true;
                $this->dump_simple_array($var);
                return $this->buffer;
            }

            if(is_null($var)){
                return "<span class='span_var span_null'>null</span>";
            }

            if(is_bool($var)){
                if($var){
                    return "<span class='span_var span_bool bool_true'>true</span>";
                } else {
                    return "<span class='span_var span_bool bool_false'>false</span>";
                }
            }

            if(is_string($var)){
                return "<span class='span_var span_string'>\"".$var."\"</span>"; 
            }

            return $var;
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
            $border_radius = '5px';

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
                                $(this).parent().toggleClass('extended');
                            });
                            $('.object_header').click(function(){
                                $(this).parent().toggleClass('extended');
                            });
                        });
                    </script>

                    <style>

                        body{
                            padding:10px;
                            margin:0px;
                            font-family: Monaco, Arial;
                            font-size:14px;
                            color:".$text_color.";
                        }

                        .block{
                            margin-top:2px;
                            margin-bottom:2px;
                            border-radius:".$border_radius.";
                        }

                        table {
                            border-collapse: collapse;
                            font-size:13px;
                        }

                        thead{
                            background:#EEE;
                        }
                          
                        table, th, td {
                            border: 1px solid ".$border_table_color.";
                        }

                        table.table_object th, table.table_object td{
                            vertical-align: top;
                        }

                        .noborder{
                            border: 0px;
                        }

                        .group_content{
                            display:none;
                            padding-left:20px;
                        }

                        .group.extended>.group_content{
                            display:block;
                        }

                        .group_header{
                            cursor:pointer;
                            padding-left:3px;
                            user-select:none;
                        }

                        .group>.group_header>.fa-minus-square-o{
                            display:none;
                        }

                        .group.extended>.group_header>.fa-minus-square-o{
                            display:inline;
                        }

                        .group.extended>.group_header>.fa-plus-square-o{
                            display:none;
                        }

                        .group{
                            border-left:2px solid ".$border_color.";
                        }

                        .object_header{
                            cursor:pointer;
                            padding-left:3px;
                            user-select:none;
                        }

                        .object_content{
                            display: none;
                        }

                        .object.extended>.object_content{
                            display:block;
                        }

                        .object>.object_header>.fa-minus-square-o{
                            display:none;
                        }

                        .object.extended>.object_header>.fa-minus-square-o{
                            display:inline;
                        }

                        .object.extended>.object_header>.fa-plus-square-o{
                            display:none;
                        }

                        .dump{
                            padding:5px;
                            margin:3px;
                            border:1px solid ".$border_color.";
                        }

                        .timestamp{
                            font-style:italic;
                            border:1px solid #EEE;
                            background:#fffed1;
                            padding:5px;
                        }

                        .scrollblock{
                            max-width:100%;
                            overflow:hidden;
                            overflow-x:auto;
                        }

                        .label{
                            padding:3px;
                        }

                        .infoblock{
                            padding:5px;
                        }

                        .infoblock div{
                            width:20px;
                            height:20px;
                            display:inline-block;
                        }

                        .infoblock i{
                            font-size:16px;
                        }

                        .block-error{
                            color:#FFF;
                            background:#b80c00;
                        }

                        .block-warning{
                            background:#ffdb4a;
                        }

                        .block-info{
                            background:#adf1ff;
                        }

                        .bool_false{
                            color:#8c0005;
                        }

                        .bool_true{
                            color:#0e7303;
                        }

                        .span_string{
                            color:#c7254e;
                        }

                        .span_null{
                            font-style:italic;
                            color:#0341fc;
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

    class Animal {

        public $gender = '';
        public $name = '';
        public $owners = Array();
        public $cubs = 0;
        public $mentor;
        public $some;

        public function feed(){

        }

    }

    class Human {
        public $name;
        public $phone;
    }

    $human = new Human;
    $human->name = 'Nelly';
    $human->phone = '911';

    $animal = new Animal;
    $animal->gender = 'male';
    $animal->name = 'Sissy';
    $animal->owners = Array('Bobb','Harley');
    $animal->mentor = $human;
    $data1 = Array(
        
    );

    $test1 = "1213";
    $test2 = Array(1,2,43,32423,6623,43,32423,6623,1,2,43,32423,6623,43,32423,34, 34,345, 567,234, 233, 23423, 34,236623,43,32423,6623,Array(33,33,33));
    $test3 = Array(
        Array('type'=>'cat', 'alive'=>true,     'gender'=>'male',   'name'=>'Ricky'),
        Array('type'=>'cat', 'alive'=>true,     'gender'=>'male',   'name'=>'Bobby','owners'=>Array('Sam','Lucy','Vahtang')),
        Array('type'=>'dog', 'alive'=>true,     'gender'=>'female', 'name'=>'Lucky'),
        Array('type'=>'dog', 'alive'=>false,    'gender'=>'male',   'name'=>'Demon','cubs'=>6),
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

    $logger->open_group('WELLS');
    $logger->print_error('Something wrong','404');
    $logger->print_warning('Something wrong');

    $logger->open_group('INNER');
    $logger->print_info('Its okay, dont worry');
    $logger->close_group();

    $logger->close_group();

    $logger->dump_object($animal);

    file_get_contents();

    