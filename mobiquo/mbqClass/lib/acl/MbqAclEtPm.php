<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtPm');

/**
 * private message acl class
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtPm extends MbqBaseAclEtPm {
    
    public function __construct() {
    }
    
    /**
     * judge can get_box_info
     *
     * @return  Boolean
     */
    public function canAclGetBoxInfo() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_box
     *
     * @return  Boolean
     */
    public function canAclGetBox($oMbqEtPmBox) {
        return MbqMain::hasLogin();
    }
  
}

?>