<?php

include __DIR__."/searchPach.php";


$subjectPaths=new SearchPath();
$replayPaths=new SearchPath();


function getHtml($url, $para = array())
{
    //echo "getHtml   :".$url."\n";

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

    ////echo $url;
    $r=curl_exec($ch);
    return $r;
}
function postHtml($url)
{
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
    $r=curl_exec($ch);
    return $r;
}


function preg_getCore($str)
{
    $parment='%<li(?:[\s]{1,})(?:.*?)j_thread_list(?:[\s]*)clearfix(?:.*?)>(.*?)</li>%s';
    //$num = preg_match_all($parment, $str, $mach);
    $mach = getStringPregContentAll($str, $parment);
    ////echo $num;
    //return $mach[0];
    return $mach;
}

function getSubjectTitle($str)
{
    $parment='%<a(?:[\s]{1,})(?:.*?)title="([^\"\']*)"%';

    $mach=getStringPregContent($str, $parment, 1);
    return $mach;
}


function getSubjectReplyNum($str)
{
    $parment='%<span(?:[\s]{1,})(?:.*?)class="threadlist_rep_num(?:[\s]*)center_text"(?:.*?)>(.*?)</span>%s';


    $mach=getStringPregContent($str, $parment, 1);
    return $mach;
}

function getAuthor($str)
{
    $parment='%<span(?:[\s]{1,})(?:.*?)class="tb_icon_author(?:.*?)title=(?:[\s]{0,})"(.*?)"%s';
    $mach=getStringPregContent($str, $parment, 1);
    return $mach;
}

function getSubjectTime($str)
{
    $parment='%<span(?:[\s]{1,})(?:.*?)class="pull-right(?:.*?)is_show_create_time"(?:.*?)>(.*?)</span>%s';

    $mach=getStringPregContent($str, $parment);

    return $mach;
}

function getLinkInfo($str)
{
    $contentUrlP='%<div(?:[\s]{1,})(?:.*?)id=(?:.*?)frs_list_pager(?:.*?)>(.*?)</div>%s';

    $content=getStringPregContent($str, $contentUrlP);


    $linkPattern='%<a(?:[\s]{1,})(?:.*?)href="(.*?)"%s';

    return getStringPregContentAll($content, $linkPattern);
}
/*
*
* gnum 0. all 其他代表返回第几个分组all
*
 */
function getStringPregContent($str, $pattern, $gnum = 1)
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
function getStringPregContentAll($str, $pattern, $gnum = 1)
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

function getSubjectContent($str)
{
    $pattern='%<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content_main d_post_content_firstfloor(?:.*?)<div(?:[^>]*?)class=(?:[^>]*?)d_post_content(?:[^>]*?)j_d_post_content(?:[^>]*?)clearfix(?:[^>]*?)>(.*?)</div>%s';


    $mach=getStringPregContent($str, $pattern);
    return $mach;
}



function getReplyContent($subjectInfo)
{
    global $replayPaths;
    $url=$subjectInfo['url'];
    if ($replayPaths->isRunPath($url)) {
        echo "169";
        return array();
    }
    $replayPaths->setRun($url);
    var_dump($replayPaths->isRunPath($url));
    $html=getHtml($url);

    $machs=array();

    $pattern='%<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content_main(?:.*?)<div(?:[^>]*?)class=(?:[^>]*?)d_post_content_main(?:.*?)<div(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)d_post_content(?:[\s]{1,})j_d_post_content(?:[\s]{1,})clearfix(?:[^>]*?)>(.*?)</div>%s';
    $mach=getStringPregContentAll($html, $pattern);

    $machs = array_merge($machs, $mach);

    $linksContentPattent='%<li(?:[\s]{1,})(?:[^>]*?)class=(?:[^>]*?)l_pager(?:[^>]*?)pager_theme_5 pb_list_pager(?:[^>]*?)>(.*?)</li>%s';

    $linksContent=getStringPregContent($html, $linksContentPattent);
    $linkPattern='%<a(?:[\s]{1,})(?:[^>]*?)href=(?:[\s]{0,})"(.*?)"(?:[^>]*?)>%s';

    $links = getStringPregContentAll($linksContent, $linkPattern);
    $newLinks=array();
    foreach ($links as $value) {
        /*
        $mix=parse_url($value);
        if(!isset($mix['host'])){
            $mix = parse_url($url);
            $value='http://'.$mix['host'].'/'.$value;
        }*/

        $newLinks[]=getIncludeUrl($url, $value);
    }

    $replayPaths->insertPaths($newLinks);

    foreach ($newLinks as $value) {
        $subjectInfo['url']=$value;
        $mach = getReplyContent($subjectInfo);
        var_dump($mach);
        $machs = array_merge($machs, $mach);
        var_dump($machs);
    }

    return $machs;
}



/*
*
*
* 获取主题内容
* 1.获取主题内容
* 2.获取每条回复内容
* 3.进入评论下一页
* 4.执行（2-3）
*
*
 */
function getSubjectPage(&$subjectInfo)
{
    global $replayPaths;
    $url=$subjectInfo['url'];
    if ($replayPaths->isRunPath($url)) {
        return true;
    }

    $html=getHtml($url);
    $subjectContent=getSubjectContent($html);
    $subjectInfo['content']=$subjectContent;

    getReplyContent($subjectInfo);
}

function getSubjectUrl($str)
{
    $pattern='%<a(?:[\s]{1,})(?:[^>]*?)href="([^>]*?)"(?:[^>]*?)class=(?:[^>]*?)j_th_tit(?:[^>]*?)>%s';

    $mach = getStringPregContent($str, $pattern, 1);

    return $mach;
}

function getIncludeUrl($fatherUrl, $url)
{
    $mix=parse_url($url);
    if (!isset($mix['host'])) {
        $fmix = parse_url($fatherUrl);

        $url='http://'.$fmix['host'].'/'.$url;
    }
    return $url;
}


/*
*
*
* 1.获取当前主题列表页面信息
* 2.提取出关联的下个主题列表页面信息
* 3.提取本页相关主题信息。（标题，回复，作者，时间,url)
* 4.循环进去每个主题页面.
* 5.提取主题页面内容
*     5.1 提取主题内容
*     5.2 提取回复主题内容
*     5.3 提取每个主题，主题回复 评论内容
* 6.记录主题页面内容
* 7.记录每个主题回复信息。
* 8.进下下个主题列表页
* 9.重复（1-8)
*
 */

function getHtmlSubjectsInfo($url)
{
    global $subjectPaths,$replayPaths;

    if ($subjectPaths->isRunPath($url)) {
        return true;
    }
    $subjectPaths->setRun($url);


    $str=getHtml($url);
    $mach=preg_getCore($str);
    $subjectInfos=array();

    foreach ($mach as $value) {
        $subjectInfo=array();
        $subjectInfo['title'] = getSubjectTitle($value);
        $subjectInfo['num'] = getSubjectReplyNum($value);
        $subjectInfo['time'] = getSubjectTime($value);
        $subjectInfo['author'] = getAuthor($value);
        $surl = getSubjectUrl($value);


        $subjectInfo['url'] = getIncludeUrl($url, $surl);

        
        $replayPaths->insertPath($subjectInfo['url']);

        $subjectInfos[]=$subjectInfo;

        getSubjectPage($subjectInfo);
    }

    var_dump($subjectInfos);

    $links = getLinkInfo($str);
    var_dump($links);
    foreach ($links as $link) {
        $link=getIncludeUrl($url, $link);
        $subjectPaths->insertPath($link);
        getHtmlSubjectsInfo($link);
    }



    return $subjectInfos;
}


//$url="http://tieba.baidu.com/f?kw=%C2%C3%D0%D0";
$url="http://tieba.baidu.com/f?kw=%C2%C3%D0%D0";
//$str=getHtml($url);
//$mach= preg_getTitle($str);
$info =getHtmlSubjectsInfo($url);
//var_dump($info);

//var_dump($mach);
