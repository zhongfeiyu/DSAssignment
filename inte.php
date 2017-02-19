<?php

class interpreter{
    private $codes;
    private $varis = array();
    private $funcs = array();

    public function load($value){
        $this->codes = explode("\n",$value);
    }

    public function process(){
        while($code = array_shift($this->codes)){
            $sentence = explode(' ',$code);
            if(($keys = array_keys($sentence,"")) != null){
                foreach($keys as $val) {unset($sentence[$val]);}
                $sentence = array_merge($sentence);
            }
            if($sentence[0] == 'ASSIGN'){
                $result = array();
                foreach($sentence as $key=>$value){
                    if($key == 0 || $key == 1) continue;
                    $count = 1;
                    foreach($this->varis as $k=>$v){
                        if(($pos = strpos($value,$k)) !== false){
                            if(($pos+strlen($k) == strlen($value) || in_array($value[$pos+strlen($k)],['+','-','*','/']))
                            && ($pos == 0 || in_array($value[$pos-1],['+','-','*','/'])))
                                $value = str_replace($k,$v,$value,$count);
                        }
                    }
                    array_push($result,$value);
                }
                $this->varis[$sentence[1]] = eval('return '.implode(' ',$result).';');
                echo 'Assigning '.$this->varis[$sentence[1]].' to '.$sentence[1].'</br>';
            }
            elseif($sentence[0] == 'DEFINE'){
                $name = $sentence[1];
                $this->funcs[$name] = array();
                while(($code = array_shift($this->codes)) != 'END'){
                    array_push($this->funcs[$name],$code);
                }
                echo 'Defining '.$name.'</br>';
            }
            elseif($sentence[0] == 'CALL'){
                $name = $sentence[1];
                $this->codes = array_merge($this->funcs[$name],$this->codes);
                echo 'Calling '.$name.'</br>';
            }
            elseif($sentence[0] == 'FOR'){
                $pos = ($a = array_search('ASSIGN',$sentence)) != null ? $a :array_search('CALL',$sentence);
                $result = array();
                $son = '';
                foreach($sentence as $key=>$value){
                    if($key == 0) continue;
                    elseif($key < $pos){
                        $count = 1;
                        if($this->varis !=null)foreach($this->varis as $k=>$v){
                            if(($pos = strpos($value,$k)) !== false){
                                if(($pos+strlen($k) == strlen($value) || in_array($value[$pos+strlen($k)],['+','-','*','/']))
                                    && ($pos == 0 || in_array($value[$pos-1],['+','-','*','/'])))
                                    $value = str_replace($k,$v,$value,$count);
                            }
                        }
                        array_push($result,$value);
                    }
                    else $son .= $value.' ';
                }
                $time = eval('return '.implode(' ',$result).';');
                for($i = 0;$i<$time;$i++) array_unshift($this->codes,$son);
            }
            else continue;
        }
    }
}
$a = new interpreter();
$a->load($_POST['content']);
$a->process();