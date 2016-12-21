<?php

abstract class testAb{
    const tieba=1;
    static public function testA(){
        echo "print public testA";
    }
};

//testAb::testA();


class testClass implements IteratorAggregate {
    private $data;
    public function __construct(){
        $data=range(1,20);
    }

    public function getIterator(){
        return $this->data;
    }

}
/*
echo testAb::tieba;
echo "\n";
$version='v2.0';

$v= preg_replace('/[^\d]/s','',$version);

//var_dump($arr);

$v=str_pad($v,5,0);
$v=(int)$v;
echo $v;
*/
/*
var_dump(testClass::class);
$name="./rules.php";
echo dirname($name);
*/
$n=12100;
$n=$n/10000;
$n=number_format($n,1);
echo $n;


?>

