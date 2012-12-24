<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForumPost');

/**
 * forum post write class
 * 
 * @since  2012-8-21
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtForumPost extends MbqBaseWrEtForumPost {
    
    public function __construct() {
    }
    
    /**
     * add forum post
     *
     * @param  Mixed  $var($oMbqEtForumPost or $objsMbqEtForumPost)
     */
    public function addMbqEtForumPost(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            $data['title'] = '(Untitled)';
            $data['rawtext'] = $var->postContent->oriValue;
            $data['parentid'] = $var->topicId->oriValue;
            $data['created'] = vB::getRequest()->getTimeNow();
            $result = vB_Api::instance('content_text')->add($data);
            if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                $var->postId->setOriValue($result);
            } else {
                MbqError::alert('', "Can not save!", '', MBQ_ERR_APP);
            }
            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $var = $oMbqRdEtForumPost->initOMbqEtForumPost($var->postId->oriValue, array('case' => 'byPostId'));    //for get state
        }
    }
    
    /**
     * modify forum post
     *
     * @param  Mixed  $var($oMbqEtForumPost or $objsMbqEtForumPost)
     */
    public function mdfMbqEtForumPost(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            $data['title'] = $var->postTitle->oriValue;
            $data['parentid'] = $var->topicId->oriValue;
            $data['rawtext'] = $var->postContent->oriValue;
            $result = vB_Api::instance('content_text')->update($var->postId->oriValue, $data);
            if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
            } else {
                MbqError::alert('', "Can not save!", '', MBQ_ERR_APP);
            }
            $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
            $var = $oMbqRdEtForumPost->initOMbqEtForumPost($var->postId->oriValue, array('case' => 'byPostId'));    //for get state
        }
    }
  
}

?>