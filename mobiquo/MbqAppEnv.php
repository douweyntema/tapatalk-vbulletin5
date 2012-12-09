<?php

defined('MBQ_IN_IT') or exit;

/**
 * application environment class
 * 
 * @since  2012-7-2
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAppEnv extends MbqBaseAppEnv {
    
    /* this class fully relys on the application,so you can define the properties what you need come from the application. */
    public $rootUrl;    /* site root url */
    public $baseUrlCore;
    public $currentUserInfo;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * application environment init
     */
    public function init() {
        @ ob_start();
        define('EXTTMBQ_NO_LIMIT_DEPTH', 10000);    //define a big depth as no limit
        define('EXTTMBQ_NO_LIMIT_PERPAGE', PHP_INT_MAX);    //define a big perpage as no limit(all data)
        $this->rootUrl = vB5_Config::instance()->baseurl;
        $this->baseUrlCore = vB5_Config::instance()->baseurl_core;
        if (MbqMain::$oMbqConfig->moduleIsEnable('user')) {
            $newResult = vB_Api::instance('user')->fetchCurrentUserinfo();
            if ($newResult['userid']) {
                $this->currentUserInfo = $newResult;
                $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                $oMbqRdEtUser->initOCurMbqEtUser();
            }
        }
        @ ob_end_clean();
    }
    
    /**
     * judge has exception errors
     *
     * @param  Mixed  $v value
     * @return Boolean
     */
    public function exttHasErrors($v) {
        return ($v['errors'] && (count($v) == 1)) ? true : false;
    }
    
    /**
     * echo exception
     *
     * @param  Object  $e  exception obj
     */
    public function exttEchoException($e) {
        MbqError::alert('', 'Find exception:'.$e->getMessage());
    }
    
}

?>