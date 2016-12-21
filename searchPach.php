<?php

$globalPaths= array();

interface TravelPath
{
    public function setPathFlag($path, $flag = 0);
    public function getPathFlag($path);
    public function getNextPath($flag = 0);
    public function initPathInfo($path);
}

class DBTravelPath{
    //private $paths;

    public function initPathInfo($path){
    var_dump($path);
        $model = $path->createModel();
        $result = $model->find();    
        if(!empty($result)){
            return true;
        }
        $model->save();
    }
    public function getPathFlag($path){
        $this->initPathInfo($path);

        $model = $path->createModel();
        $result = $model->find();    
        if(false == $result){
            echo "not get getPathFlag \n";
            exit;
        }
        return $result['run'];
    }

    public function setPathFlag($path, $flag = 0){
        $this->initPathInfo($path);

        $model = $path->createModel();
        return $model->setRun($flag);
    }

    public function getNextPath($where){
        $pathModel = new PathModel();
        $result = $pathModel->find($where);
        if(false == $result){
            return false;
        }
       $path = UnitDataFactory::createUnitData($result['path'],UnitData::PATH);
       $path->pathtype=$result['type'];
       return $path;
        //return $result['path'];
    }


}


class Paths implements TravelPath
{
    private $paths=array();

    public function setPathFlag($path, $flag = 0)
    {
        $this->initPathInfo($path);
        $pkey=$this->getPathKey($path);
        $this->paths[$pkey]['flag']=$flag;
    }

    public function getPathFlag($path)
    {
        $this->initPathInfo($path);
        $pkey=$this->getPathKey($path);

        return $this->paths[$pkey]['flag'];
    }

    public function getNextPath($flag = 0)
    {
        $keys=array_keys($paths);
        foreach ($keys as $key) {
            if ($flag == $this->paths[$key]) {
                return $this->path[$key];
            }
        }
        return false;
    }

    


    private function getPathKey($path)
    {
        //file_exists($path->getKey)
        return $path->key();
        /*
        $path=trim($path);
        $md5Key=md5($path);
        return $md5Key;*/
    }

    public function initPathInfo($path)
    {
        $pkey=$this->getPathKey($path);
        if (!isset($this->paths[$pkey])) {
            //echo "path:".$path."    "."pkey:".$pkey."\n";
            $this->paths[$pkey]=array();
            $this->paths[$pkey]['flag']=0;
            $this->paths[$pkey]['path']=$path;
        }
    }
}





class SearchPath
{
    private $paths;

    public function __construct()
    {
//          $this->paths=new Paths();
          $this->paths=new DBTravelPath();
    }

    public function nextPath($where)
    {
        $path = $this->paths->getNextPath($where);
        return $path;
    }

    public function isRunPath($path)
    {
        //echo "isRunPath:".$path."   ".$this->path->getPathFlag($path);
        return  $this->paths->getPathFlag($path);
    }

    public function insertPath($path)
    {
        $this->paths->initPathInfo($path);
    }

    public function insertPaths($paths)
    {
        if (empty($paths) || !is_array($paths)) {
            return true;
        }
        foreach ($paths as $path) {
            $this->insertPath($path);
        }
    }

    public function setUnRun($path){
        $this->paths->setPathFlag($path, 0);
    }

   /*
   *
   * flag 0,no ,1 yes
   *
    */
    public function setRun($path)
    {
        echo $path."\n";
        $this->paths->setPathFlag($path, 1);
    }
}
