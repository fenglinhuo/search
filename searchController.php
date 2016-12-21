<?php
include __DIR__.'/searchPach.php';
include __DIR__.'/rules.php';
include __DIR__.'/parses.php';
include __DIR__.'/models.php';
include __DIR__."/units.php";
include __DIR__."/db.class.php";




/*
*
*  组件
*  遍历组件
*  解析组件
*  写入组件
*
*   builder  
*   策略
* 
 */




class Fetch
{
    const GET=1;
    const POST=2;
    const REQUEST_LIMIT=10;

    public function get($url, $para)
    {
        $connNum=0;
        while ($connNum<self::REQUEST_LIMIT) {
            $ch=curl_init();
            if (!($r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1))) {
                return false;
            }
            $str='';
            foreach ($para as $key => $value) {
                $str.=$key."=".$value."&";
            }
            $str=substr($str, 0, -1);
            if ($str != '') {
                $url.="?".$str;
            }

            if (!($r = curl_setopt($ch, CURLOPT_URL, $url))) {
                return false;
            }

            if (!($r = curl_setopt($ch, CURLOPT_TIMEOUT, 5))) {
                return false;
            }

    //////echo $url;
            $r=curl_exec($ch);

            if (false == $r) {
                echo "get error {$connNum} \n";

                $connNum++;
                curl_close($ch);
                continue;
            }

            curl_close($ch);

            return $r;
        }
        return false;
    }

    public function post($url, $para)
    {
        $connNum=0;
        while ($connNum<self::REQUEST_LIMIT) {
            $ch=curl_init();
            if (!($r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1))) {
                return false;
            }
            if (!($r = curl_setopt($ch, CURLOPT_URL, $url))) {
                return false;
            }
            if (!($r = curl_setopt($ch, CURLOPT_POSTFIELDS, $para))) {
                return false;
            }

            if (!($r = curl_setopt($ch, CURLOPT_TIMEOUT, 5))) {
                return false;
            }
            $r=curl_exec($ch);

            if (false == $r) {
                //echo "post error {$connNum} \n";
                $connNum++;
                curl_close($ch);
                continue;
            }

            curl_close($ch);

            return $r;
        }
        return false;
    }
    public function html($path, $para = array(), $type = self::GET)
    {
           // $url=$path->path;
        if (self::GET == $type) {
            return $this->get($path, $para);
        } else if (self::POST == $type) {
            return $this->post($path, $para);
        }
    }
}

class Controller
{

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
}*/

/*
*
* 
 */

class SearchArithmetic
{
    private $model;
    private $parses=array();
    private $searchPaths;
    private $subjectPaths;
    private $replyPaths;
    private $fetch=null;
    private $deep=5;
    private $info=array();


    public function __construct($info=null,$parses = null)
    {

        $this->info=$info;
        $this->searchPaths=new SearchPath();
        $this->subjectPaths=new SearchPath();
        $this->replyPaths=new SearchPath();


        $this->parses=$parses;
    }

    public function setFetch($fetch)
    {
        $this->fetch=$fetch;
    }

    public function setParse($parses)
    {
        $this->parses=$parses;
    }

    public function setFlag($path)
    {
        if ($this->searchPaths->isRunPath($path)) {
            return false;
        }
        $this->searchPaths->setRun($path);
        return true;
    }


    public function setSubjectFlag($path,$flag=1)
    {

        if(0==$flag){
            $this->subjectPaths->setUnRun($path);
            return true;
        }

        if ($this->subjectPaths->isRunPath($path)) {
            return false;
        }
        $this->subjectPaths->setRun($path);
        return true;
    }

    public function setReplyFlag($path,$flag=1)
    {

        if(0==$flag){
            $this->replyPaths->setUnRun($path);
            return true;
        }

        if ($this->replyPaths->isRunPath($path)) {
            return false;
        }
        $this->replyPaths->setRun($path);
        return true;
    }

//    public function searchSubject($path, $info)
    public function searchSubject($path)
    {
       // $parse=$this->parses['subject'];
       /*
       if (!($this->setFlag($path))) {
            return false;
        }*/

        if (!($this->setSubjectFlag($path))) {
            return false;
        }

        $url=$path->getPath();

        $html = $this->fetch->html($url);

        if(false == $path) {
            $this->setSubjectFlag($path,0);
            return;
        }

        $paths = $this->parses['subjectPath']->parse($html);
        $contents = $this->parses['subjectContent']->parse($html);

         foreach ($paths as $punit) {
            $punit->tieId=$this->info['tieId'];

        }




        $this->subjectPaths->insertPaths($paths);
        //echo "163";

        foreach ($contents as $content) {
            //$model = $content->createModel();
            //$replyPath = $model->get('url');
            //
            //var_dump($content);
            //echo $content."\n";

            $model = $content->createModel();
            $model->tieId=$this->info['tieId'];
            
            $sid=$model->sid;

            $content->sid=$sid;
            $model->fromUrl=$url;

            $replyPath=$content->url;
            $pathUnit=UnitData::createUnitData($replyPath, UnitData::PATH);
            $pathUnit->getIncludeUrl($path->path);
            $pathUnit->pathtype=RULE_REPLY_PATH;
            $pathUnit->tieId=$this->info['tieId'];

            $model->url=$pathUnit->path;
            $model->save();


            //var_dump($pathUnit);
            $this->replyPaths->insertPath($pathUnit);
            //echo $content."\n";
           // exit;
            do {
                $this->searchReply($pathUnit, $content);
            } while ($pathUnit=$this->replyPaths->nextPath(array('run'=>0,'type'=>RULE_REPLY_PATH,'tieId'=>$this->info['tieId'])));
        }

        return $paths;
    }

    public function searchReply($path, $subject)
    {

        if (!($this->setReplyFlag($path))) {
            return false;
        }

       // $parse=$this->parses['reply'];
        $url=$path->getPath();
        
        $html = $this->fetch->html($url);

        if(false == $html){
            $this->setReplyFlag($path,0);
            return false;
        }

        //echo "243 \n";
        $paths = $this->parses['replyPath']->parse($html);
        //echo "245 \n";
        $fatherUrl=$subject->url;


        //var_dump($fatherUrl);
        //var_dump($subject);


        foreach ($paths as $punit) {
            $punit->getIncludeUrl($path->path);
            $punit->tieId=$this->info['tieId'];

        }

        //var_dump($paths);
        //echo "193";

        $contents = $this->parses['replyContent']->parse($html);
        //echo "196";

        //echo "212";
        foreach ($contents as $content) {
            /*
            $model = $content->createModel($info);
            $model->save();
            */
           
            $info=array('sid'=>$subject->sid,'url'=>$url,'fromUrl'=>$fatherUrl);
            $model = $content->createModel($info);
            $model->save();

            $resources=$this->parses['sourceImage']->parse($content->content);
            foreach ($resources as $r) {
                $resource = new ResourceModel();
                $resource->pathtype=1;
                $resource->content=$r;
                $resource->resouceMapId=$model->resouceMapId;
                $resource->save();
                //var_dump($r);
                //exit;
            }


            //echo $content."\n";
        }

        //echo "221";
        $this->replyPaths->insertPaths($paths);

        /*
        foreach ($paths as $path) {
            $this->searchReply($path, $subject);
        }*/
        return true;
    }

    public function search(PathUnitData $path)
    {
       
       // do{
        $subjectPath=$path;
        $taskInfo=$this->info['tieId'];
        $subjectPath->tieId=$taskInfo;

        do {
            $this->searchSubject($subjectPath);
        } while ($subjectPath=$this->subjectPaths->nextPath(array('run'=>0,'type'=>RULE_SUBJECT_PATH,'tieId'=>$taskInfo)));
        # code...
        //}where($paths)
        /*
        foreach ($paths as $path) {
            $model = $path->createModel();
            $model->save();  

        }*/

/*
        foreach ($paths as $path) {
            //$nextPaths = $this->searchSubject($path);
            
            $this->search($path);
        }*/
    }
}

abstract class SearchBuilder
{
    protected $searcher;
    abstract public function builderFetch();

    abstract public function builderParse();
    abstract public function builderSearchArithmetic();

    public function getSearcher()
    {
        return $this->searcher;
    }
}

class TieBaBuilder extends SearchBuilder
{
    private $config=array(
        'parseConfig'=>array('replyContent' =>'ReplyContentRule','replyPath'=>'ReplyPathRule',
        'subjectContent'=>'SubjectContentRule','subjectPath'=>'SubjectPathRule','sourceImage'=>'SourceImageRule')
        );
    private $info=array();

    public function __construct($info){
        $this->info=$info;
    }

    public function builderFetch()
    {
        $fetch=new Fetch();
        $this->searcher->setFetch($fetch);
    }

    public function builderParse()
    {
        $parses=array();
        foreach ($this->config['parseConfig'] as $key => $value) {
            $parses[$key]=new $value();
        }
        $this->searcher->setParse($parses);
    }

    public function builderSearchArithmetic()
    {
        $this->searcher = new SearchArithmetic($this->info);
    }
}


class SearchDirector
{
    public function creatsSearcher($builder)
    {
        $builder->builderSearchArithmetic();
        $builder->builderFetch();
        $builder->builderParse();
        return $builder->getSearcher();
    }
}


class Task
{
    private $searcher;

    public function start($url)
    {

        $path=UnitData::createUnitData($url, UnitData::PATH);
        $path->pathtype=RULE_SUBJECT_PATH;
        $info=array('tieId'=>1);
        $builder=new TieBaBuilder($info);
        $director=new SearchDirector();
        $this->searcher=$director->creatsSearcher($builder);
        $this->searcher->search($path);
    }
}


$task=new Task();
//$url="http://tieba.baidu.com/f?kw=%C2%C3%D0%D0";
//$url="http://tieba.baidu.com/f?ie=utf-8&kw=%E8%BE%BD%E9%98%B3&fr=search";
$url="http://tieba.baidu.com/f?ie=utf-8&kw=%E5%8C%97%E4%BA%AC&fr=search";
$task->start($url);
