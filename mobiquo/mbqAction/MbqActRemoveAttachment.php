<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActRemoveAttachment');

/**
 * remove_attachment action
 * 
 * @since  2012-9-19
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqActRemoveAttachment extends MbqBaseActRemoveAttachment {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement() {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        //this is a dummy method,only used for remove the attachment bbcode in app
        $this->data['result'] = true;
    }
  
}

?>