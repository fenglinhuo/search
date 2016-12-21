<?php

/**/
class Parse
{
    const PATH=1;
    const CONTENT=2;
    private $rule;

    public function __construct(Rule $rule)
    {
        $this->rule=$rule;
    }


    /*
     * type: path,content
     *
     * 返回:Path 对象数组
     * 
     */
    public function parse($html)
    {
        /*
        if ($type == self::PATH) {
            //??
            return $this->rule->parsePaths($html);
        } else if ($type == self::CONTENT) {
            //??
            return $this->rule->parseContents($html);
        } else {
            return false;
        }*/
        return $this->rule->parse($html);

    }

    public function setRule($rule){
        $this->rule=$rule;
    }
}

?>