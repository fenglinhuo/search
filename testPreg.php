<?php

$str='             <img class="BDE_Image" pic_type="0" width="550" height="552" src="http://imgsrc.baidu.com/forum/w%3D580/sign=330d1b07aa773912c4268569c8188675/f861db33c895d1436845b99970f082025baf079b.jpg" pic_ext="jpeg"  ><br>这是第18代法老王朝图坦卡蒙陵墓中出土的文物。<img class="BDE_Image" pic_type="0" width="550" height="552" src="http://imgsrc.baidu.com/forum/w%3D580/sign=330d1b07aa773912c4268569c8188675/f861db33c895d1436845b99970f082025baf079b.jpg" pic_ext="jpeg"  >';

//$str="dsadwrqeqeqeqeqe";

$parment='%<img(?:[\s]{1,})(?:.*?)src="(.*?)"(?:.*?)>%s';

preg_match_all($parment,$str,$maths);
//var_dump($maths);
$name='Adsadasd';
//echo strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
$name='adad123dada';
preg_replace_callback('/\d/',function($matchs){
    var_dump($matchs);
    return 'p';
},$name);
echo $name;
?>