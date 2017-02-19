<?php

class unionFind{
    public $id;
    private $size;
    private $width;


    public function __construct($size,$width){
        $this->size = $size;
        $this->width = $width;
        for($i = 0;$i < $size ;$i++)
            $this->id[$i] = $i;
    }

    public function find($a){
        while($this->id[$a] != $a)
            $a = $this->id[$a];
        return $a;
    }

    public function findPath($a){
        $result = array();
        while($this->id[$a] != $a){
            array_push($result,$a);
            $a = $this->id[$a];
        }
        array_push($result,$a);
        return $result;
    }

    public function union($a,$b){
        $i = $this->find($a);
        $j = $this->find($b);
        if($i != $j){
            $this->id[$i] = $j;
        }
    }

    public function unioned($a,$b){
        return $this->find($a) == $this->find($b);
    }
}


class maze{
    private $width;
    private $height;
    private $unionFind;
    private $graph;
    private $visited;
    private $path = '';

    public $name;

    public function __construct($width,$height){
        $this->width = $width;
        $this->height = $height;
        $this->unionFind = new unionFind($width*$height,$width);
    }

    public function init(){
        for($i = 0;$i<2*$this->height-1;$i++){
            for($j=0;$j<2*$this->width-1;$j++){
                if(($i % 2 == 1 && $j % 2 == 0) || ($i %2 == 0 && $j % 2 == 1)){
                    $this->graph[$j][$i] = 1;
                }
            }
        }
        while(!$this->unionFind->unioned(0,$this->height*$this->width-1)){
            $pos = rand(0,$this->height*$this->width-1);
            $pos2Array = array();
            if($pos % $this->width != 0) array_push($pos2Array,$pos-1);
            if($pos % $this->width != $this->width-1) array_push($pos2Array,$pos+1);
            if($pos >= $this->width) array_push($pos2Array,$pos-$this->width);
            if($pos < $this->width*($this->height-1)) array_push($pos2Array,$pos+$this->width);

            $pos2 = $pos2Array[rand(0,sizeof($pos2Array)-1)];
            if(!$this->unionFind->unioned($pos,$pos2)){
                $this->unionFind->union($pos,$pos2);
                $pos_x = $pos %$this->width;
                $pos_y = ceil(($pos-$pos_x)/$this->width);
                $pos2_x = $pos2%$this->width;
                $pos2_y = ceil(($pos2-$pos2_x)/$this->width);
                $this->graph[$pos_x+$pos2_x][$pos_y+$pos2_y] = 0;
            }
        }
    }

    public function printMaze(){
        if($this->width*$this->height <= 400)$unit = 20;
        elseif($this->width*$this->height <= 800)$unit = 15;
        elseif($this->width*$this->height <= 1600)$unit = 10;
        else $unit = 5;
        $border = 10;
        $img = imagecreatetruecolor(($unit+1)*$this->width+2*$border,$this->height*($unit+1)+4*$border);
        $white = imagecolorallocate($img,255,255,255);
        imagefill($img,0,0,$white);
        $black = imagecolorallocate($img,0,0,0);
        imagefilledrectangle($img,$border+$unit,$border,$border+$unit*$this->width+$this->width,$border+1,$black);
        imagefilledrectangle($img,$border,$border+$unit,$border+1,$border+$unit*$this->height+$this->height,$black);
        imagefilledrectangle($img,$border+$unit*$this->width-1+$this->width,$border,$border+$unit*$this->width+$this->width,$border+$unit*$this->height+$this->height-$unit-1,$black);
        imagefilledrectangle($img,$border,$border+$unit*$this->height+$this->height-1,$border+$unit*$this->width+$this->width-$unit-1,$border+$unit*$this->height+$this->height,$black);
        foreach($this->graph as $x=>$value){
            foreach($value as $y=>$flag){
                $a = ceil($x/2)*$unit+floor($x/2)+$border;
                $b = ceil($y/2)*$unit+floor($y/2)+$border;
                if($flag && $x%2 == 0 && $y%2 == 1)
                    imagefilledrectangle($img,$a,$b,$a+$unit,$b+1,$black);
                if($flag && $x%2 == 1 && $y%2 == 0)
                    imagefilledrectangle($img,$a,$b,$a+1,$b+$unit,$black);
            }
        }
        $way = $this->way();
        if(strlen($way) <= 2*$this->width) imagestring($img,2,$border,$border*2+($unit+1)*$this->height,'Path: '.$way,$black);
        elseif(strlen($way) <= 3*$this->width){
            imagestring($img,2,$border,$border*1.5+($unit+1)*$this->height,'Path: '.substr($way,0,strlen($way)/2),$black);
            imagestring($img,2,$border,$border*2.5+($unit+1)*$this->height,'      '.substr($way,strlen($way)/2+1),$black);
        }
        else{
            imagestring($img,2,$border,$border*0.9+($unit+1)*$this->height,'Path: '.substr($way,0,floor(strlen($way)/3)),$black);
            imagestring($img,2,$border,$border*1.9+($unit+1)*$this->height,'      '.substr($way,floor(strlen($way)/3)+1,floor(strlen($way)/3)),$black);
            imagestring($img,2,$border,$border*2.9+($unit+1)*$this->height,'      '.substr($way,floor(strlen($way)/3)*2+1),$black);
        }

        header("Content-type: image/png");
        imagepng($img,'src/'.$_POST['filename'].'.png');
    }

    private function DFS($x,$y){
        $this->visited[$x][$y] = 1;
        if($x == $this->width-1 && $y == $this->height-1){
            return 'success';
        }
        if(array_key_exists($x,$this->visited) && array_key_exists($y-1,$this->visited[$x]) &&
            $this->visited[$x][$y-1] == 0 && $this->graph[$x+$x][$y+$y-1] == 0){
            if($this->DFS($x,$y-1) == 'success'){
                $this->path = 'N'.$this->path;
                return 'success';
            }
        }
        if(array_key_exists($x+1,$this->visited) && array_key_exists($y,$this->visited[$x+1]) &&
            $this->visited[$x+1][$y] == 0 && $this->graph[$x+$x+1][$y+$y] == 0){
            if($this->DFS($x+1,$y)  == 'success'){
                $this->path = 'E'.$this->path;
                return 'success';
            }
        }
        if(array_key_exists($x,$this->visited) && array_key_exists($y+1,$this->visited[$x]) &&
            $this->visited[$x][$y+1] == 0 && $this->graph[$x+$x][$y+$y+1] == 0){
            if($this->DFS($x,$y+1) == 'success'){
                $this->path = 'S'.$this->path;
                return 'success';
            }
        }
        if(array_key_exists($x-1,$this->visited) && array_key_exists($y,$this->visited[$x]) &&
            $this->visited[$x-1][$y] == 0 && $this->graph[$x+$x-1][$y+$y] == 0){
            if($this->DFS($x-1,$y) == 'success'){
                $this->path = 'W'.$this->path;
                return 'success';
            }
        }
        return 'fail';
    }

    public function way(){

        for($i = 0;$i<$this->height;$i++){
            for($j=0;$j<$this->width;$j++){
                    $this->visited[$j][$i] = 0;
            }
        }
        $this->DFS(0,0);
        return $this->path;
    }

    public function remove(){
        $filesnames = scandir(dirname(__FILE__).'/src/');
        foreach($filesnames as $name){
            echo $name;
            if(substr($name,0,13) < $_POST['filename']) unlink(dirname(__FILE__).'/src/'.$name);
        }
    }
}

$m = new maze($_POST['width'],$_POST['height']);
$m->remove();
$m->init();
$m->printMaze();
//echo $m->way();