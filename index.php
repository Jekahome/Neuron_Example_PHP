<?php




class neuron
{
    private $id;
    public $axon;//out excitation transfer
    public  $dendrite;//input excitation
    public $weight;
    public function getId(){
        return $this->id;
    }
}

function matrix_three($a){
    return (($a[0][0]*$a[1][1]*$a[2][2])+($a[0][1]*$a[1][2]*$a[2][0])+($a[0][2]*$a[1][0]*$a[2][1])-($a[0][2]*$a[1][1]*$a[2][0])-($a[0][0]*$a[1][2]*$a[2][1])-($a[0][1]*$a[1][0]*$a[2][2]));
}

class pixel{

    public $x;//горизонт
    public $y;//вертикаль
    public $color;
    public $id;
    public function __construct($x,$y,$color)
    {
        $this->x=$x;
        $this->y=$y;
        $this->color=$color;
        $this->id=microtime(1);
    }
    public function __toString()
    {
        return "x:".$this->x.", y:".$this->y.", color:".$this->color;
    }
}

class AllPixel{
    public $arr_pixel;
    public $minX;
    public $maxX;
    public $minY;
    public $maxY;
    public $lines;
    private static $ist;
    public static  function init(){
        if(!self::$ist instanceof AllPixel)self::$ist=new AllPixel();
        return self::$ist;
    }

    public function save($x,$y,$color)
    {
        if(!isset($this->arr_pixel[$x.$y])){
            $this->arr_pixel[$x.$y]= new pixel($x,$y,$color);

            if($this->minX<$x)$this->minX=$x;
            if($this->maxX>$x)$this->maxX=$x;
            if($this->minY<$y)$this->minY=$y;
            if($this->maxY>$y)$this->maxY=$y;
        }

    }
    public function __destruct()
    {
        if(!empty($this->arr_pixel)){
            $path='serialize'.time().'.txt'; fopen($path,'w+b');
            file_put_contents($path,serialize($this->arr_pixel));
        }
    }

    public function reload($path){
        $this->arr_pixel= unserialize( file_get_contents($path)) ;
    }

    public function getNextX($obj){
        foreach ($this->arr_pixel as $pixel){
                if($pixel->y==$obj->y &&  $pixel->x==$obj->x+1)return $pixel;
        }
        return false;
    }
    public function getNextY($obj){
        foreach ($this->arr_pixel as $pixel){
            if($pixel->x==$obj->x &&  $pixel->y==$obj->y+1)return $pixel;
        }
        return false;
    }
    public function buildLines(){
        //сбор по общему направлению
       $AllLine= AllLine::init();
        //echo 'X|Y|Color'."\n";
            foreach ($this->arr_pixel as $one_pixel){
                //echo $one_pixel->x."|".$one_pixel->y."|".$one_pixel->color."\n";
            if($pixel=$this->getNextY($one_pixel)){
                if($pixel instanceof pixel){
                    $AllLine->addPixelToLine($one_pixel,Line::Y);
                    $AllLine->addPixelToLine($pixel,Line::Y);
                }
            }
        }

        foreach ($this->arr_pixel as $one_pixel){
            if($pixel=$this->getNextX($one_pixel)) {
                if ($pixel instanceof pixel) {
                    $AllLine->addPixelToLine($one_pixel, Line::X);
                    $AllLine->addPixelToLine($pixel, Line::X);
                }
            }
        }
    }
}


class Line{
    public $head;
    public $arr_pixel=[];
    public $type;
    public $id;
    const  X='X' ;
    const  Y='Y' ;
    const  ZZero='ZZero' ;
    const  ZNZero='ZNZero' ;
    public function __construct($type)
    {
        $this->id=microtime(1);
        $this->type=$type;
    }
    public function addPixel(pixel $pixel,$type){

        if($this->type==$type){
            foreach ($this->arr_pixel as $pixel_){
                if($pixel->y==$pixel_->y && $pixel->x==$pixel_->x)return false;
            }
             array_push($this->arr_pixel,$pixel);
        }

        return $this;
    }

    public function isPixel(pixel $pixel,$type){
        if($type==Line::Y){
             foreach ($this->arr_pixel as $pixel_){
                 if($pixel->y-1==$pixel_->y && $pixel->x==$pixel_->x)return true;
             }
            return false;

        }else if($type==Line::X){
            foreach ($this->arr_pixel as $pixel_){
                if($pixel->x-1==$pixel_->x && $pixel->y==$pixel_->y)return true;
            }
            return false;
        }
    }

    public function __toString()
    {
        $temp='--------------------'."\n";
        $temp.='Line:'.$this->id."\n";
        $temp.='count:'.count($this->arr_pixel)."\n";
        $temp.="type:".$this->type."\n";
        foreach ($this->arr_pixel as $pixel)
                 $temp.=$pixel."\n";
        return  $temp;
    }

}


class AllLine{
    public $lines;
    private static $ins;
    private function __construct(){}
    public static function init(){
    if(!self::$ins instanceof AllLine)self::$ins=new AllLine();
        return self::$ins;
    }

    public function addPixelToLine(pixel $pixel,$type ){
        if(empty($this->lines[$type])){
            $this->lines[$type]=[];
            $this->lines[$type][]= (new Line($type))->addPixel($pixel,$type);
        }else{
           $flag=false;
            foreach ($this->lines[$type] as &$line){
                if($line->isPixel($pixel,$type)){
                    $line->addPixel($pixel,$type);
                    $flag=true;
                }
            }
        if(!$flag){
                $this->lines[$type][]= (new Line($type))->addPixel($pixel,$type);
            }
        }
    }


    /**
     * точки соединения
     * связать с линиями и найти соотношение длин и углов линий
     */
    public function junction_point(){
        foreach ($this->lines as $line){
            foreach ($line as $line_){
                foreach ($line_->arr_pixel as $pixel){
                    $this->f($pixel,$line_->id);
                   // echo 'ушло:',$pixel->id,"|",$line_->id,"\n";
                }
            }
        }
    }

    function f($point,$id ){
        foreach ($this->lines as $line){
            foreach ($line as $line_){
                if($line_->id!=$id){
                    foreach ($line_->arr_pixel as $pixel){
                        if($pixel->id==$point->id)echo $point."\n";
                        //echo 'сравниваю:',$pixel->id,"|",$line_->id,"\n";
                    }
                }

            }
        }



    }

}


class supperimg{

    public $img;
    public $path;
    public function __construct()
    {

    }

    public function genericMatrix($a){
        $h=$w=0;
        $h=count($a);
        foreach ($a as $v){
            foreach ($v as $v2){
                $w++;
            }
            break;
        }
         //echo $h."|".$w;


        $Y1=0;$X1=0;
        $Y2=2;$X2=2;
        $all=[];
        while($Y2<$h){
            //echo $Y1."|".$Y2."|".$X1."|".$X2."\n";
            $res=[];
            for ($y=0;$y<count($a);$y++){

                if( $y>$Y2 || $y<$Y1 )continue;
                for ($x=0;$x<count($a[$y]);$x++){

                    if( $x>$X2 || $x<$X1 ) continue;

                    $res[]=$a[$y][$x];
                }
            }
            $all[]=$res;
            $X1++;
            $X2++;

            if($X2>=$w){
                $Y2++;
                $Y1++;
                $X1=0;
                $X2=2;
            }


        }

        return $all;
    }

    public function pixelStep(){
        $path = 'number/four.png';
        $size = getimagesize($path);
        $img = imagecreatefrompng($path);
        $AllPixel= AllPixel::init();

        $h=$size[1];
        $w=$size[0];
        for ($y=0;$y<$h;$y++){

            for ($x=0;$x<$w;$x++){

                if(imagecolorat($img,$x,$y)==0) // Получение индекса цвета пиксела  $a[$y][$x];
                {
                    //echo "COLOR:".$x,"|",$y,"\n";
                    $AllPixel->save($x,$y,imagecolorat($img,$x,$y));
                }else {
                    //echo $x,"|",$y,"\n";
                }
            }
        }
    }

    public function part(){
        $path = 'number/four_350.png';
        $size = getimagesize($path);
        $img = imagecreatefrompng($path);
        $AllPixel= AllPixel::init();

        $h=$size[1];
        $w=$size[0];
       if($h < 10 || $w<10)throw new Exception('Information:smal size');
       // echo round($w/6,0,PHP_ROUND_HALF_DOWN),"x",round($h/6,0,PHP_ROUND_HALF_DOWN)."\n";
        // СБОР ЗНАЧЕНИЙ ПИКСЕЛЕЙ
        $PIXELS=[];
        for ($y=0;$y<$h;$y++){
            $temp_X=[];
            for ($x=0;$x<$w;$x++){
                $temp_X[$x]=imagecolorat($img,$x,$y);//?0:1;
            }
            $PIXELS[]=$temp_X;
        }
  //echo "<pre>";print_r($PIXELS);exit;
        if(0){
            // ПИКСЕЛЬНЫЙ ВЫВОД
            for ($y=0;$y<count($PIXELS);$y++){

                for ($x=0;$x<count($PIXELS[$y]);$x++){
                    echo $PIXELS[$y][$x]." ";
                    //print_r($PIXELS[$y][$x]);exit;
                }
                echo "<br>";
            }
        }


        if(1){
            $part_block=10;
            $en_w =  round($w/$part_block,0,PHP_ROUND_HALF_DOWN);
            $en_h = round($h/$part_block,0,PHP_ROUND_HALF_DOWN);

            printf("Block:W:%d;H:%d,COUNT:%d",$en_w,$en_h,$en_w*$part_block*$en_h*$part_block);
            $c=0;$v=0;
            $GROUP=[];
            $block=0;
            $skip_h=$skip_w=0;
            while($h>$skip_h-$en_h){
                if($c>$part_block-1){$c=0;$v++; }
                $skip_w= ($en_w*$c);
                $skip_h=($v*$en_h) ;
                for ($y=0;$y<count($PIXELS);$y++){
                    if($y<$skip_h||$y>=$skip_h+$en_h)continue;
                    for ($x=0;$x<count($PIXELS[$y]);$x++){
                        if($x<$skip_w ||$x>=$skip_w+$en_w)continue;
                        //if($PIXELS[$y][$x]==0)
                        $GROUP[$block][]=$PIXELS[$y][$x];
                    }
                }
                $c++;
                $block++;
            }

            $AVG=[];$count=0;$plus=0;
            foreach ($GROUP as $key=>$block){
                foreach ($block as $numb){
                    if($numb==0){
                        if(!isset( $AVG[$key])) $AVG[$key]=0;
                        $AVG[$key]++;
                        $count++;
                    }
                }
                if(!isset( $AVG[$key])) $AVG[$key]=0;
                else $AVG[$key]=$AVG[$key]/count($block);
                if($AVG[$key]>0)$plus++;
                $count+=$AVG[$key];
            }
            $AVG=($count/$plus);
            $AVG=$AVG-$AVG/5;
             echo "<pre>"; print_r($AVG);

            $RESULT=[];
            foreach ($GROUP as $key=>$block){

                $count=0;
                foreach ($block as $numb){
                    if($numb==0)$count++;
                }
                if($count>=$AVG)$RESULT[$key]=0;
                else $RESULT[$key]=1;
               /* if($count>=($en_w*$en_h)/50)$RESULT[$key]=0;
                else $RESULT[$key]=1;*/

                /*if($count>1)$RESULT[$key]=0;
                else $RESULT[$key]=1;*/

            }
           // echo "<pre>";  print_r($RESULT);


            echo '
            <style>table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
            td{
            width: 20px;
            text-align: center;
            }
            </style>';

            echo "<table>";
            $count=0;
            foreach ($RESULT as $k=>$numb){

                if($count==0){echo  "<tr>";}
                if($count==10){echo  "</tr><tr>";$count=0;}
                if($numb==0)
                 echo  "<td style='background-color: aquamarine'>";
                 else echo  "<td>";
                echo $numb,"</td>";


                $count++;
            }
            echo "<table>";
        }


        // TEST
        if(0){
           $PIXELS=[];
            $PIXELS[0]=[2,2,3,3,4,4,5,5,6,6,1,3,1,3,1,3,44,33,5,5];
            $PIXELS[1]=[7,7,8,8,9,9,0,0,0,0,0,0,0,0,0,0,0,0,3,3];
            $PIXELS[2]=[66,66,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[3]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[4]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[5]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[6]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[7]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[8]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[9]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[10]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[11]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[12]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[13]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[14]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[15]=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[16]=[222,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[17]=[333,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            $PIXELS[18]=[777,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,3,3];
            $PIXELS[19]=[888,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,4,3,3];
            $w=20;$h=20;


            $part_block=10;
            $en_w =  round($w/$part_block,0,PHP_ROUND_HALF_DOWN);
            $en_h = round($h/$part_block,0,PHP_ROUND_HALF_DOWN);
echo $w."|".$h."<br>";
            printf("Block:W:%d;H:%d,COUNT:%d",$en_w,$en_h,$en_w*$part_block*$en_h*$part_block);
            $c=0;$v=0;
            $GROUP=[];
            $block=0;
            $skip_h=$skip_w=0;

            while($h>$skip_h-$en_h){
                if($c>$part_block-1){$c=0;$v++; }
                $skip_w= ($en_w*$c);
                $skip_h=($v*$en_h) ;
                for ($y=0;$y<count($PIXELS);$y++){
                    if($y<$skip_h||$y>=$skip_h+$en_h)continue;
                    for ($x=0;$x<count($PIXELS[$y]);$x++){
                        if($x<$skip_w ||$x>=$skip_w+$en_w)continue;
                        //if($PIXELS[$y][$x]==0)
                        $GROUP[$block][]=$PIXELS[$y][$x];
                    }
                }
                $c++;
                $block++;
            }

            //echo "<pre>";echo count($GROUP );print_r($GROUP );

            $RESULT=[];
            foreach ($GROUP as $key=>$block){

                $count=0;
                foreach ($block as $numb){
                    if($numb==0)$count++;
                }
                if($count>=($en_w*$en_h)/2)$RESULT[$key]=0;
                else $RESULT[$key]=1;
            }
            echo "<pre>";
            print_r($RESULT);
        }



       if(0){

        header ("Content-type: image/png");
        $img = imagecreatetruecolor($w, $h);
        $ink = imagecolorallocate($img, 255, 255, 255);
        for ($y=0;$y<count($PIXELS);$y++){
            for ($x=0;$x<count($PIXELS[$y]);$x++){
                if($PIXELS[$y][$x]==0) imagesetpixel($img,$x,$y,$ink);
            }
        }
        imagepng($img);
        imagedestroy($img);
    }
       if(0){
          header ("Content-type: image/png");
           $path = 'number/four2.png';
           $size = getimagesize($path);
           $img = imagecreatefrompng($path);
           $h=$size[1];
           $w=$size[0];

           $PIXELS=[];
           for ($y=0;$y<$h;$y++){
               $temp_X=[];
               for ($x=0;$x<$w;$x++){
                   //$temp_X[$x]=imagecolorat($img,$x,$y);// нормальный вывод
                   $temp_X[$x]=imagecolorat($img,$x,$y)>0?0:16777215;//перевернутый чернобелый
               }
               $PIXELS[]=$temp_X;
           }

           $img = imagecreatetruecolor($w, $h);

           for ($y=0;$y<count($PIXELS);$y++){
               for ($x=0;$x<count($PIXELS[$y]);$x++){
                   //if($PIXELS[$y][$x]==0) imagesetpixel($img,$x,$y,$ink);
                   //Получение индекса цвета пиксела 16777215
                   //$color_index = imagecolorat($img, $x, $y);
                   $color_index =$PIXELS[$y][$x];
                    // делаем его удобочитаемым
                    $color_tran = imagecolorsforindex($img, $color_index);
                   $ink = imagecolorallocate($img, $color_tran['red'], $color_tran['green'], $color_tran['blue']);
                    //echo  $color_tran['red'], $color_tran['green'], $color_tran['blue']."<br>";
                   imagesetpixel($img,$x,$y,$ink);
               }
           }
           imagepng($img);
           imagedestroy($img);
       }

    }


    public function test(){
        $path = 'number/four.png';
        $size = getimagesize($path);
        $img = imagecreatefrompng($path);
        // $size[0]// width
        //$size[1] //height

        //$x = 1;//горизонт
        //$y = 1;//вертикаль
        $AllPixel= AllPixel::init();

        $h=$size[1];
        $w=$size[0];

        $Y1=0;$X1=0;
        $Y2=2;$X2=2;
        $all=[];
        while($Y2<$h){
            //echo $Y1."|".$Y2."|".$X1."|".$X2."\n";
            $res=[];
            for ($y=0;$y<$h;$y++){
                if($y>$Y2||$y<$Y1)continue;
                for ($x=0;$x<$w;$x++){
                    if($x>$X2||$x<$X1) continue;
                    $res[]=imagecolorat($img,$x,$y); // Получение индекса цвета пиксела  $a[$y][$x];
                    $AllPixel->save($x,$y);
                }
            }
            $all[]=$res;
            $X1++;
            $X2++;

            if($X2>=$w){
                $Y2++;
                $Y1++;
                $X1=0;
                $X2=2;
            }
        }

        foreach ($all as $val){
           // $this->
        }
        return $all;
        //****************
       /* for ($i = 1; $i < $size[0] * $size[1]; $i++) {
            $color_index = imagecolorat($img, $x, $y); //Получение индекса цвета пиксела 16777215
            $x++;
            if ($x > $size[0]) {
                $x = 1;
                $y++;
            }
        }*/
    }

    public function f(){
        $path = 'number/two.png';
        $size = getimagesize($path);
        $img = imagecreatefrompng($path);
        // $size[0]// width
        //$size[1] //height

        $x = 1;//горизонт
        $y = 1;//вертикаль
        $color = array();
        for ($i = 1; $i < $size[0] * $size[1]; $i++) {

            $color_index = imagecolorat($img, $x, $y); //Получение индекса цвета пиксела 16777215
// делаем его удобочитаемым
            $color_tran = imagecolorsforindex($img, $color_index);

            if (($color_tran['red'] == 0 && $color_tran['green'] == 0 && $color_tran['blue'] == 0)
                or
                ($color_tran['red'] <= 255 && $color_tran['red'] >= 235 &&
                    $color_tran['green'] <= 255 && $color_tran['green'] >= 235 &&
                    $color_tran['blue'] <= 255 && $color_tran['blue'] >= 235)
                or
                ($color_tran['red'] >= 190 && $color_tran['red'] <= 230 &&
                    $color_tran['green'] >= 225 && $color_tran['green'] <= 245 &&
                    $color_tran['blue'] >= 245 && $color_tran['blue'] <= 255)
            ) {
                $color[$x][$y] = 0;
            } else {
                $color[$x][$y] = 1;
            }

            $x++;
            if ($x > $size[0]) {
                $x = 1;
                $y++;
            }
        }
    }
}


//print_r(getimagesize('number/two.png'));
//print_r(imagecolorat(imagecreatefrompng('number/two.png'), 250, 250));
//f();

/*
0010000010
0010000010
0010000010
0010000010
0011111110
0000000010
0000000010
0000000010
0000000010
0000000010
*/

$supperimg = new supperimg();
$supperimg->part();

exit;

 $supperimg = new supperimg();
 $supperimg->pixelStep();
 $AllPixel = AllPixel::init();
 $AllPixel->buildLines();

 $AllLine= AllLine::init();
 //print_r([$AllLine->lines]);
/*foreach ($AllLine->lines as $line){
    foreach ($line as $line_){
        echo $line_;
    }
}*/

$AllLine->junction_point();

/*
$Y=[];
$X=[];
$y=[];
for($i=0;$i<10;$i++){
    $x=[];
    for($z=0;$z<10;$z++){
        if($i==4 && ($z>2 && $z<8 )){$x[]=1;}
        elseif(($z==2 || $z==8 )&& $i<5 ){$x[]=1;}
        elseif($z==8 && $i>4){$x[]=1;}
        else $x[]=0;
    }
    $y[]=$x;
}

for($i=0;$i<count($y);$i++){
    $a=[];
    for($z=0;$z<count($y[$i]);$z++){
        if($y[$i][$z]==0)echo '.';
        else echo $y[$i][$z];
        $a[]=$y[$i][$z];
    }
    echo "\n";
}

$a=[];
$a[]=[1,-2,3];
$a[]=[4,0,6];
$a[]=[-7,8,9];

 //echo ($a[0][0]*$a[1][1]*$a[2][2])+($a[0][1]*$a[1][2]*$a[2][0])+($a[0][2]*$a[1][0]*$a[2][1])-($a[0][2]*$a[1][1]*$a[2][0])-($a[0][0]*$a[1][2]*$a[2][1])-($a[0][1]*$a[1][0]*$a[2][2]);
//echo matrix($a);
//echo ($a[0][0]*$a[1][1]*$a[2][2])."+".($a[0][1]*$a[1][2]*$a[2][0])."+".($a[0][2]*$a[1][0]*$a[2][1])."-".($a[0][2]*$a[1][1]*$a[2][0])."-".($a[0][0]*$a[1][2]*$a[2][1])."-".($a[0][1]*$a[1][0]*$a[2][2]);

function d($a){
    $h=$w=0;
    $h=count($a);
    foreach ($a as $v){
        foreach ($v as $v2){
            $w++;
        }
        break;
    }
//echo $h."|".$w;

    $Y1=0;$X1=0;
    $Y2=2;$X2=2;
    $all=[];
    while($Y2<$h){
        //echo $Y1."|".$Y2."|".$X1."|".$X2."\n";
        $res=[];
        for ($y=0;$y<count($a);$y++){

            if( $y>$Y2 || $y<$Y1 )continue;
            for ($x=0;$x<count($a[$y]);$x++){

                if( $x>$X2 || $x<$X1 ) continue;

                $res[]=$a[$y][$x];
            }
        }
            $all[]=$res;
            $X1++;
            $X2++;

            if($X2>=$w){
                $Y2++;
                $Y1++;
                $X1=0;
                $X2=2;
            }

   }
return $all;
}

$a=[];
$a[]=[1,2,3,4];
$a[]=[5,6,7,8];
$a[]=[9,10,11,12];
$a[]=[13,14,15,16];

$arr=d($a);
print_r($arr);
*/