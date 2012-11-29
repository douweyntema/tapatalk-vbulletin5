<?php

defined('MBQ_IN_IT') or exit;

/**
 * common method class
 * 
 * @since  2012-7-2
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqCm extends MbqBaseCm {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * transform timestamp to iso8601 format
     *
     * @param  Integer  $timeStamp
     * TODO:need to be made more general.
     */
    public function datetimeIso8601Encode($timeStamp) {
        //return date("c", $timeStamp);
        return date('Ymd\TH:i:s', $timeStamp).'+00:00';
    }
    
    /**
     * get short content
     *
     * @param  String  $str
     * @param  Integer  $length
     * @return  String
     * TODO:need to be made more useful.
     */
    public function getShortContent($str, $length = 200) {
        $str = preg_replace('/\[url.*?\].*?\[\/url.*?\]/', '[url]', $str);
        $str = preg_replace('/\[img.*?\].*?\[\/img.*?\]/', '[img]', $str);
        $str = preg_replace('/[\n\r\t]+/', ' ', $str);
        
        $str = preg_replace('/\[\/?.*?]/s', '', $str);
    
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        $str = function_exists('mb_substr') ? mb_substr($str, 0, $length) : substr($str, 0, $length);
        
        $str = strip_tags($str);
        return $str;
    }
    
}

?>