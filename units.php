<?php



class UnitDataFactory
{
    public static function createUnitData($data, $type)
    {
        switch ($type) {
            case UnitData::PATH:
                return new PathUnitData($data);
                break;
            case UnitData::REPLY:
                return new ReplyUnitData($data,$type);
                break;
            case UnitData::SUBJECT:
                return new SubjectUnitData($data,$type);
                break;
            default:
                return null;
                break;
        }
    }
}

/* type : path,reply,subject */
class UnitData
{
    public $data=array();
    public $type;
    const PATH=1;
    const REPLY=2;
    const SUBJECT=3;

    public function createModel($info = array())
    {
        foreach ($info as $key => $value) {
            $this->data[$key]=$value;
        }

        //return Model::create($this);
        
        return $this->model();
    }


    public static function createUnitData($data, $type)
    {
        return UnitDataFactory::createUnitData($data, $type);
    }

    public function setType($type){
        $this->type=$type;
    }

    public function __construct($data, $type)
    {
        $this->data=$data;
        //$this->type=$type;
        $this->setType($type);
    }

    public function __set($name,$value){
        $this->data[$name]=$value;
    }

    public function __get($name){
        if(isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    public function __toString(){
        $str=__CLASS__;
        foreach ($this->data as $key => $value) {
             $str=" ".$key.":".$value.",";
        }
        return $str;
    }

}

class PathUnitData extends UnitData
{

    public function __construct($url){
        parent::__construct(array('type'=>UnitData::PATH,'path'=>$url),UnitData::PATH);
        $this->key=$this->key();
    }    

    public function model(){
        return new PathModel($this);
    }

    public function getPath()
    {
        //return $this->data['path'];
        return $this->path;
    }

    public function key(){
        $path=$this->path;
        $path=trim($path);
        $md5Key=md5($path);
        return $md5Key;
    }

    public function getIncludeUrl($fatherUrl)
    {
        $mix=parse_url($this->path);
        if (!isset($mix['host'])) {
            $fmix = parse_url($fatherUrl);
            $path=$this->path;
            if(substr($path,0,1) == '/'){
                $path=substr($path,1);
            }

            $this->path='http://'.$fmix['host'].'/'.$path;
        }
        return $this->path;
    }

}

class ReplyUnitData extends UnitData{
    public function model(){
        return new ReplyModel($this);
    }
}

class SubjectUnitData extends UnitData{
    public function model(){
        return new SubjectModel($this);
    }
}


/*
class Path
{
    private $type;
    private $path;
    public function __construct($path, $type = 1)
    {
        $this->type=$type;
        $this->path=$path;
    }

    public function createModel()
    {
        $UnitData = UnitData::createUnitData(array('type'=>$this->type,'path'=>$this->path), UnitData::PATH);
        return $UnitData->createModel();
        //return $UnitData;
    }

    public function getType()
    {
        return $this->type;
    }
    public function getPath()
    {
        return $this->path;
    }
}
*/



