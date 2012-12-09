<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForumTopic');

/**
 * forum topic acl class
 * 
 * @since  2012-8-10
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtForumTopic extends MbqBaseAclEtForumTopic {
    
    public function __construct() {
    }
    
    /**
     * judge can get topic from the forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclGetTopic($oMbqEtForum) {
        return true;
    }
    
    /**
     * judge can get thread
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclGetThread($oMbqEtForumTopic) {
        return true;
    }
    
    /**
     * judge can get_user_topic
     *
     * @return  Boolean
     */
    public function canAclGetUserTopic() {
        if (MbqMain::$oMbqConfig->getCfg('user.guest_okay')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
  
}

?>