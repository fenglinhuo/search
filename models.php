<?php

$dbConfig=array('user'=>'root','pass'=>'123456','host'=>'localhost','dbName'=>'test');

abstract class Model
{
    protected $property=array();
    protected $tableName;
    protected $db=null;
    protected $recordKey=null;

    const DATA_TYPE='dataType';
    const DATA='data';


    const DATA_FUNCTION=1;
    const DATA_VALUE=2;
    const DATA_MAP=3;




    public function set($key, $value)
    {
        $this->property[$key]=$value;
    }

    public function get($key)
    {
        if (!isset($this->property[$key])) {
            return null;
        }
        return $this->property[$key];
    }

    public function __set($key,$value){
        $this->property[$key]=$value;

    }

    public function __isset($key){
        if(isset($this->property[$key])){
            return true;
        }
        return false;
    }

    public function __get($key)
    {
        if (!isset($this->property[$key])) {
            return null;
        }
        return $this->property[$key];
    }

    public function kid(){
        return rand(1001, 9999).time().rand(1001, 9999);
    }

    public function __construct($data=null)
    {
        global $dbConfig;
        //var_dump($data);
        if(null != $data) {
            foreach ($data->data as $key => $value) {
                $this->set($key, $value);
            }
        }
        $this->db= new db($dbConfig);
    }

    public function save($data = null)
    {
        if(null!=$data) {
           $insertData=$data; 
        }else {
            $insertData=$this->getInsertData();
        }

        $this->db->insert($this->tableName, $insertData);
    }

    public function update($where,$data){
       return $this->db->update($this->tableName,$where,$data);
    }


    public function find($where){
        return $this->db->find($this->tableName,$where);
    }


    public function getInsertData()
    {
        if (null == $this->recordKey || empty($this->recordKey)) {
            return $this->property;
        }

        $data=array();
        foreach ($this->recordKey as $key => $value) {
            // 假如没有值 .抛出异常
            if (is_array($value)) {
                if (isset($value[MODEL::DATA])) {
                    if (is_array($value[MODEL::DATA])) {
                        $d=$value[MODEL::DATA];

                        if (isset($value[MODEL::DATA_TYPE])) {
                            switch ($value[MODEL::DATA_TYPE]) {
                                case MODEL::DATA_VALUE:
                                    foreach ($d as $key => $value) {
                                        $data[$key]=$value;
                                    }
                                    break;
                                case MODEL::DATA_FUNCTION:
                                    foreach ($d as $key => $value) {
                                        $data[$key]=$this->$value();
                                    }
                                    break;
                                case MODEL::DATA_MAP:
                                    foreach ($d as $key => $value) {
                                        $data[$key]=$this->$value;
                                    }

                                    break;


                                default:
                                    # code...
                                    foreach ($d as $key => $value) {
                                        $data[$key]=$value;
                                    }
                                    break;
                            }
                        } else {
                            echo "set model data not set MODEL::DATA_TYPE \n";
                        }
                    } else {
                        echo "data is not set MODEL::DATA a array \n";
                    }
                } else {
                    echo "data is not set MODEL::DATA \n";
                }

               // $data[$k]=$this->$v;
            } else {
                $data[$value]=$this->$value;
            }
        }

        return $data;
    }

    public function init()
    {
    }
}


class ReplyModel extends Model
{
    protected $tableName='reply';
    protected $recordKey=array(
        'sid',
        'content',
        //array(MODEL::DATA=>array('resouceMapId'=>0),MODEL::DATA_TYPE=>MODEL::DATA_VALUE),
        'url',
        'resouceMapId',
        'fromUrl'
        );

    public function __get($name){
        switch ($name) {
            case 'resouceMapId':
            if(!isset($this->_resouceMapId)){
                $this->_resouceMapId=$this->kid();
            }
            return $this->_resouceMapId;
            break;            
            default:
                return parent::__get($name);
                break;
        }
    }

    public function __set($name,$value){
        switch ($name) {
            case 'resouceMapId':
                $this->_resouceMapId=$value;
            break;            
            default:
                return parent::__set($name,$value);
                break;
        }
    }

/*
     public function __get($name)
    {
        switch ($name) {
            case 'sid':
                return $this->sid();
            break;
            default:
                return parent::__get($name);
                # code...
            break;
        }
    }

     public function sid()
    {
        if(!isset($this->kid)){
            $this->kid=time().rand(1001, 9999);
        }
        return $this->kid;

    }*/


    /*
    public function __construct(UnitData $data)
    {
    }*/
    /*
    public function save($tableName=null,$data=null)
    {
    }*/
}

class PathModel extends Model
{
    protected $tableName='path';
    protected $recordKey=array(
        'path',
        'key',
        array(MODEL::DATA=>array('type'=>'pathtype'),MODEL::DATA_TYPE=>MODEL::DATA_MAP),
        'tieId'
        //array(MODEL::DATA=>array('run'=>0),MODEL::DATA_TYPE=>MODEL::DATA_VALUE),
        );
/*
    public function key()
    {
    }*/


    public function exists(){
        $data=array('path'=>$this->path,'key'=>$this->key);
        $return = $this->db->find($this->tableName,$data);
        if(!empty($return)){
            return true;
        }else{
            return false;
        }
    }

    public function find($where=null){
        if(null == $where){
            $where=array('key'=>$this->key,'type'=>$this->pathtype);
        }
        return parent::find($where);
    }

    public function updatePath($data){
        $where=array('path'=>$this->path,'key'=>$this->key);
        return parent::update($where,$data);
    }

    public function setRun($flag){
        $data=array('run' => $flag );
        return $this->updatePath($data);
    }

    public function key(){
        $path=$this->path;
        $path=trim($path);
        $md5Key=md5($path);
        return $md5Key;
    }

    public function __get($name){
        switch ($name) {
            case 'key':
            return $this->key();
            
            default:
            return parent::__get($name);
        }
    }

/*
    public function __construct(UnitData $data)
    {
        foreach ($data->$data as $key => $value) {
            $this->set($key, $value);
        }
    }*/
}

class ResourceModel extends Model
{
    protected $tableName='resource';
    protected $recordKey=array(
        array(MODEL::DATA=>array('type'=>'pathtype'),MODEL::DATA_TYPE=>MODEL::DATA_MAP),
        'content',
        'resouceMapId'
        );

     public function __get($name){
        switch ($name) {
            case 'resouceMapId':
            if(!isset($this->_resouceMapId)){
                $this->_resouceMapId=$this->kid();
            }
            return $this->_resouceMapId;
            break;            
            default:
                return parent::__get($name);
                break;
        }
    }

    public function __set($name,$value){
        switch ($name) {
            case 'resouceMapId':
                $this->_resouceMapId=$value;
            break;            
            default:
                return parent::__set($name,$value);
                break;
        }
    }

}

class SubjectModel extends Model
{
    protected $tableName='subject';
    protected $recordKey=array(
        'url',
        'title',
        'sid',
        array(MODEL::DATA=>array('pub_time'=>'time','replyNum'=>'num','anchor'=>'author'),MODEL::DATA_TYPE=>MODEL::DATA_MAP),
        'resouceMapId',
       // array(MODEL::DATA=>array('resouceMapId'=>0),MODEL::DATA_TYPE=>MODEL::DATA_VALUE),
        'fromUrl',
        'tieId'
        );

/*
    public function __construct($data){
        parent::__construct($data);
    }*/


    public function __get($name)
    {
        switch ($name) {
            case 'sid':
                return $this->sid();
            break;
            case 'resouceMapId':
                if(!isset($this->_resouceMapId)){
                    $this->_resouceMapId=$this->kid();
                }
                return $this->_resouceMapId;
            break;
            default:
                return parent::__get($name);
                # code...
            break;
        }
    }


    public function sid()
    {
        if(!isset($this->_sid)){
            $this->_sid=$this->kid();
        }

        return $this->_sid;

        /*
        $sid=time().rand(1001, 9999);
        return $sid;*/

    }

    /*
    public function __construct(UnitData $data)
    {
    }*/
/*
    public function save()
    {
    }*/
}
