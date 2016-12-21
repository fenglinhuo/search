<?php
define('RULE_SUBJECT_PATH',1);
define('RULE_REPLY_PATH',2);


interface Rule
{
    public function parsePaths($html);
    public function parseContents($html);
}

abstract class BaseRule
{
    /*
*
* gnum 0. all 其他代表返回第几个分组all
*
 */
    public function getStringPregContent($str, $pattern, $gnum = 1)
    {
        $re=preg_match($pattern, $str, $mach);
        if (false == $re) {
            return false;
        }
        if ($gnum) {
            return $mach[$gnum];
        } else {
            return $mach;
        }
    }

    public function getStringPregContentAll($str, $pattern, $gnum = 1)
    {
        $num = preg_match_all($pattern, $str, $mach);
        if (0==$num) {
            return array();
        }
        if ($gnum) {
            return $mach[$gnum];
        } else {
            return $mach;
        }
    }

    public function arrayToPaths($links)
    {
        $paths=array();

        foreach ($links as $link) {
            //$path=new Path($link);
            $path = UnitData::createUnitData($link,UnitData::PATH);
            $paths[]=$path;
        }
        return $paths;
    }

    public function getIncludeUrl($fatherUrl, $url)
    {
        $mix=parse_url($url);
        if (!isset($mix['host'])) {
            $fmix = parse_url($fatherUrl);

            $url='http://'.$fmix['host'].'/'.$url;
        }
        return $url;
    }
}

class SubjectRule extends BaseRule implements Rule
{

    protected function preg_getCore($html)
    {
        $parment='%<li(?:[\s]{1,})(?:.*?)j_thread_list(?:[\s]*)clearfix(?:.*?)>(.*?)</li>%s';
    //$num = preg_match_all($parment, $str, $mach);
        $mach = $this->getStringPregContentAll($html, $parment);
    ////echo $num;
    //return $mach[0];
        return $mach;
    }
    function getSubjectTitle($str)
    {
        $parment='%<a(?:[\s]{1,})(?:.*?)title="([^\"\']*)"%';

        $mach=$this->getStringPregContent($str, $parment, 1);
        return $mach;
    }

    function getSubjectReplyNum($str)
    {
        $parment='%<span(?:[\s]{1,})(?:.*?)class="threadlist_rep_num(?:[\s]*)center_text"(?:.*?)>(.*?)</span>%s';


        $mach=$this->getStringPregContent($str, $parment, 1);
        return $mach;
    }

    function getSubjectTime($str)
    {
        $parment='%<span(?:[\s]{1,})(?:.*?)class="pull-right(?:.*?)is_show_create_time"(?:.*?)>(.*?)</span>%s';

        $mach=$this->getStringPregContent($str, $parment);

        return $mach;
    }

    function getAuthor($str)
    {
        $parment='%<span(?:[\s]{1,})(?:.*?)class="tb_icon_author(?:.*?)title=(?:[\s]{0,})"(.*?)"%s';
        $mach=$this->getStringPregContent($str, $parment, 1);
        return $mach;
    }

    function getSubjectUrl($str)
    {
        $pattern='%<a(?:[\s]{1,})(?:[^>]*?)href="([^>]*?)"(?:[^>]*?)class=(?:[^>]*?)j_th_tit(?:[^>]*?)>%s';

        $mach = $this->getStringPregContent($str, $pattern, 1);

        return $mach;
    }

    function getLinkInfo($str)
    {
        $contentUrlP='%<div(?:[\s]{1,})(?:.*?)id=(?:.*?)frs_list_pager(?:.*?)>(.*?)</div>%s';

        $content=$this->getStringPregContent($str, $contentUrlP);


        $linkPattern='%<a(?:[\s]{1,})(?:.*?)href="(.*?)"%s';

        return $this->getStringPregContentAll($content, $linkPattern);
    }

    public function parsePaths($html)
    {
        $links=$this->getLinkInfo($html);
        $paths = $this->arrayToPaths($links);
        foreach($paths as $path){
            $path->pathtype=RULE_SUBJECT_PATH;
        }
        return $paths;
    }

    public function parseContents($html)
    {
        $core=$this->preg_getCore($html);
        $UnitDatas=array();
        foreach ($core as $value) {
            $subjectInfo['title'] = $this->getSubjectTitle($value);
            $subjectInfo['num'] = $this->getSubjectReplyNum($value);
            $subjectInfo['time'] = $this->getSubjectTime($value);
            $subjectInfo['author'] = $this->getAuthor($value);
            $subjectInfo['url'] = $this->getSubjectUrl($value);

            $UnitData = UnitData::createUnitData($subjectInfo, UnitData::SUBJECT);
            $UnitDatas[]=$UnitData;

           // $subjectInfo['url'] = getIncludeUrl($url, $surl);
        }
        return $UnitDatas;
    }
}

class SubjectPathRule extends SubjectRule
{
    public function parse($html){
        return $this->parsePaths($html);
    }
}

class SubjectContentRule extends SubjectRule{
    public function parse($html){
        return $this->parseContents($html);
    }
}



class ReplyRule extends BaseRule implements Rule
{

    function getSubjectContent($str)
    {
        $pattern='%<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content_main d_post_content_firstfloor(?:.*?)<div(?:[^>]*?)class=(?:[^>]*?)d_post_content(?:[^>]*?)j_d_post_content(?:[^>]*?)clearfix(?:[^>]*?)>(.*?)</div>%s';


        $mach=$this->getStringPregContent($str, $pattern);
        return $mach;
    }

    function getReplyPath($html)
    {
        $linksContentPattent='%<li(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)l_pager(?:[^>]*?)pager_theme_5 pb_list_pager(?:[^>]*?)>(.*?)</li>%s';

        $linksContent=$this->getStringPregContent($html, $linksContentPattent);
        $linkPattern='%<a(?:[\s]{1,})(?:[^>]*?)href=(?:[\s]{0,})"(.*?)"(?:[^>]*?)>%s';

        $links = $this->getStringPregContentAll($linksContent, $linkPattern);
        return $links;
    }

    function getReplyContents($html)
    {
        $pattern='%<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content_main(?:.*?)<div(?:[^>]*?)class=(?:[^>]*?)d_post_content_main(?:.*?)<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content(?:[\s]{1,})j_d_post_content(?:[\s]{1,})clearfix(?:[^>]*?)>(.*?)</div>%s';
        $mach=$this->getStringPregContentAll($html, $pattern);
        return $mach;
    }


    public function parsePaths($html)
    {
        //??
        $links=$this->getReplyPath($html);

        $paths= $this->arrayToPaths($links);

        foreach($paths as $path){
            $path->pathtype=RULE_REPLY_PATH;
        }


        return $paths;
    }

    public function parseContents($html)
    {
        //??
        $contents=$this->getReplyContents($html);

        $UnitDatas=array();
        foreach ($contents as $content) {
            $replyInfo=array();
            $replyInfo['content']=$content;
            $UnitDatas[] = UnitData::createUnitData($replyInfo, UnitData::REPLY);
        }
        return $UnitDatas;
    }
}

class ReplyPathRule extends ReplyRule{
    public function parse($html){
        return $this->parsePaths($html);
    }
}

class ReplyContentRule extends ReplyRule{
    public function parse($html){
        return $this->parseContents($html);
    }
}

class SourceImageRule extends BaseRule{

    public function parse($html){
        $pattern='%<img(?:[\s]{1,})(?:.*?)src="(.*?)"(?:.*?)>%s';
        $mach=$this->getStringPregContentAll($html, $pattern);
        
        if(null == $mach){//$math == null or array()
            return array();
        }

        return $mach;
    }
}



?>