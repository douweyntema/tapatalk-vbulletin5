<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetForum');

/**
 * get_forum action
 * 
 * @since  2012-8-3
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqActGetForum extends MbqBaseActGetForum {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement() {
        //$data = vB_Api::instance('content_channel')->fetchTopLevelChannelIds();
        //$data = vB_Api::instance('content_channel')->fetchChannelById(12);
        //$data = vB_Api::instance('node')->fetchChannelNodeTree(0, 100, 1, 10000);
        parent::actionImplement();
    }
  
}

?>