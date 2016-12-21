<?php

class db
{
    private $host=null;
    private $user=null;
    private $pass=null;
    private $conn=null;
    private $dbName=null;
    private $connStr=null;


    function __construct($config = null)
    {
        $this->connect($config);
    }

    public function connect($config = null)
    {
        if (false == $this->constructConn($config)) {
            return false;
        }

        try {
            $this->conn=new PDO($this->connStr, $this->user, $this->pass, array(PDO::ATTR_PERSISTENT=>true,PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            $this->query('set NAMES utf8mb4');
            $this->query('set character_set_server=utf8mb4');
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function close()
    {
        $this->conn=null;
    }


    protected function constructConn($config = null)
    {
        if (null == $config) {
            return $this->connstr();
        } else {
            foreach ($config as $key => $value) {
                $this->$key=$value;
            }
            return $this->connstr();
        }
    }

    public function selectDb($dbName)
    {
        $sql="use {$dbName}";
        $this->dbName=$dbName;
        $this->conn->exec($sql);
    }

    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            $this->$name=$value;
            return true;
        }
        return false;
    }


    protected function connstr()
    {
        if ((null == $this->host) || (null == $this->user) || (null == $this->pass)) {
            return false;
        }

        if (null != $this->dbName) {
            $this->connStr="mysql:host={$this->host};dbname={$this->dbName}";
        } else {
            $this->connStr="mysql:host={$this->host}";
        }

        return true;
    }

    public function select($tableName,$where)
    {

    }

    public function find($tableName,$where=null,$order=null)
    {
        if(null == $where){
                    $findSql="select * from {$tableName}";
        }else {
            if(is_array($where)){
                $whereArr=array();
                foreach($where as $key=>$value){
                    $whereArr[]='`'.$key."`='".$value."'";
                }
                $whereStr=implode(" and ",$whereArr);
            }else{
                $whereStr=$where;
            }
            $findSql="select * from {$tableName} where {$whereStr}";
        }
        echo $findSql;
       $stmt = $this->conn->prepare($findSql);
       $br = $stmt->execute();
       if(false ===$br){
        echo "107";
        return false;
       }
       $num=$stmt->rowCount();
       if(0===$num){
        return array();
       }
       $result = $stmt->fetch();
       if(false === $result){
        echo "112";
        return false;
       }
       return $result;
    }

    public function update($tableName,$where,$data){
        if(null == $where){
                   // $findSql="select * from {$tableName}";
                   echo "db update error where == null \n";
                   return false;
        }else {
            if(is_array($where)){
               $whereArr=array();
                foreach($where as $key=>$value){
                    $whereArr[]='`'.$key."`='".$value."'";
                }
                $whereStr=implode(" and ",$whereArr);
            }else{
                $whereStr=$where;
            }

            if(is_array($data)){
                $dataArr=array();
                foreach($data as $key=>$value){
                    $dataArr[]='`'.$key."`='".$value."'";
                }
                $dataArr=implode(",",$dataArr);
            }else{
                $dataArr=$data;
            }


            $findSql="update {$tableName} set {$dataArr} WHERE {$whereStr}";
           // $findSql="update * from {$tableName} where {$whereStr}";
        }
       $stmt = $this->conn->prepare($findSql);
       $br = $stmt->execute();
       if(false === $br){
        return false;
       }
       return true;

    }

    public function query($query){
        $stmt = $this->conn->query($query);
        if(false == $stmt){
            return false;
        }        
        return $stmt;
    }

    public function insert($tableName, $data)
    {
        /*
        $num=func_num_args();
        $args=func_get_args();

        switch ($num) {
            case 1:
                $data=$args[0];
                break;
            case 2:
                $tableName=$args[0];
                $data=$args[1];
                break;
            default:
                return false;
            break;
        }*/



        $ks=array_keys($data);
        $keys=array();
        foreach ($ks as $value) {
            $keys[]='`'.$value.'`';
        }

        $values=array_values($data);

        $keyStr=implode(',', $keys);
        $valueStr=str_repeat('?,', count($values));
        $valueStr=substr($valueStr, 0, -1);

        $insertSql="insert into {$tableName}({$keyStr}) values({$valueStr})";

        $stmt = $this->conn->prepare($insertSql);
        echo $insertSql."\n";
        foreach ($values as $key => $value) {
            $k=$key+1;
            echo $k." ".$value.' '."\n";
            $stmt->bindValue($k, $value);
        }
        $stmt->execute();
    }
}

/*

$config=array('user'=>'root','pass'=>'123456','host'=>'localhost');
$db=new db($config);
$db->selectDb('test');
var_dump($db);
//$arr=array('t'=>12,'t2'=>45,'shareCount'=>234,'status'=>3,'isinform'=>2);
$arr=array('anchor'=>'死大大大');
//$db->insert('subject', $arr);
//$db->query("set character_set_connection=utf8");
//$db->query("set character_set_client=utf8");

$query="show variables like '%char%';";
$stmt = $db->query($query);
foreach ($stmt as $row) {
    var_dump($row);
}
/*
$arr=array('anchor'=>'死大大大');
$db->insert('subject', $arr);
$updateArr=array('anchor'=>'bjn');
$db->update('subject',$arr,$updateArr);
$result = $db->find('subject',$arr);
var_dump($result);

*/


