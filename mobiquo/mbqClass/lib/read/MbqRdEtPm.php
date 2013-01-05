<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtPm');

/**
 * private message read class
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtPm extends MbqBaseRdEtPm {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtPm, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get private message box objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtPmBox() {
        try {
            $result = vB_Api::instanceInternal('content_privatemessage')->listFolders();
            if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                $objsMbqEtPmBox = array();
                foreach ($result as $k => $v) {
                    if ($oMbqEtPmBox = $this->initOMbqEtPmBox($k, array('case' => 'byBoxId', 'displayTitle' => $v))) {
                        $objsMbqEtPmBox[] = $oMbqEtPmBox;
                    }
                }
                return $objsMbqEtPmBox;
            } else {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm box.');
            }
        } catch (Exception $e) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm box.');
        }
    }
    
    /**
     * init one private message box by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byBoxId' means init pm box by boxId
     * $mbqOpt['displayTitle'] means box display title
     * @return  Mixed
     */
    public function initOMbqEtPmBox($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byBoxId') {
            try {
                $result = vB_Api::instanceInternal('content_privatemessage')->getFolderInfoFromId($var);
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                    $result = array_shift($result);
                    $oMbqEtPmBox = MbqMain::$oClk->newObj('MbqEtPmBox');
                    $oMbqEtPmBox->boxId->setOriValue($result['folderid']);
                    $oMbqEtPmBox->boxName->setOriValue($mbqOpt['displayTitle']);
                    if ($result['title'] == 'messages' && $result['iscustom'] == 0) {   //inbox
                        $oMbqEtPmBox->boxType->setOriValue(MbqBaseFdt::getFdt('MbqFdtPm.MbqEtPmBox.boxType.range.inbox'));
                        $oMbqEtPmBox->unreadCount->setOriValue(vB_Api::instanceInternal('content_privatemessage')->getUnreadInboxCount());
                    } elseif ($result['title'] == 'sent_items' && $result['iscustom'] == 0) {   //sent
                        $oMbqEtPmBox->boxType->setOriValue(MbqBaseFdt::getFdt('MbqFdtPm.MbqEtPmBox.boxType.range.sent'));
                        $oMbqEtPmBox->unreadCount->setOriValue(0);
                    } elseif ($result['iscustom'] == 1) {   //custom
                        $oMbqEtPmBox->unreadCount->setOriValue(0);
                    } else {    //other
                        return false;
                    }
                    $count = vB_Api::instanceInternal('content_privatemessage')->getFolderMsgCount($oMbqEtPmBox->boxId->oriValue);
                    $oMbqEtPmBox->msgCount->setOriValue($count['count']);
                    return $oMbqEtPmBox;
                } else {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm box.');
                }
            } catch (Exception $e) {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm box.');
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * get private message objs
     * TODO:not finished,implement conversation instead of pm,so do not need to finish this method,only used for debug and code review.
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byBox' means get data by pm box obj.$var is the pm box obj.
     * $mbqOpt['case'] = 'byArrPmRecord' means get data by arrPmRecord.$var is the arrPmRecord.
     * $mbqOpt['case'] = 'byPmIds' means get data by pm ids.$var is the ids.
     * @return  Mixed
     */
    public function getObjsMbqEtPm($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byBox') {
            $oMbqEtForum = $var;
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                try {
                    $result = vB_Api::instanceInternal('content_privatemessage')->listMessages(array(
                        "folderid" => $var->boxId->oriValue,
                        "page" => $oMbqDataPage->curPage,
                        "perpage" => $oMbqDataPage->numPerPage,
                        "sortDir" => 'desc'
                    ));
                    if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                        //MbqCm::writeLog(print_r(vB_Api::instanceInternal('node')->getFullContentforNodes(array(47)), true));    //read from id first,find starter id for the second step
                        //MbqCm::writeLog(print_r(vB_Api::instanceInternal('content_privatemessage')->getMessage(47), true));   //read from id second
                        //MbqCm::writeLog(print_r(vB_Api::instanceInternal('content_privatemessage')->getFullContent(46), true));
                        MbqError::alert('', 'eded');
                    } else {
                        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm.');
                    }
                } catch (Exception $e) {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Can not get pm.');
                }
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
                $search = array("channel" => $var->forumId->oriValue);
                $search['view'] = vB_Api_Search::FILTER_VIEW_TOPIC;
                $search['depth'] = 1;
                //$search['depth'] = EXTTMBQ_NO_LIMIT_DEPTH;
                if ($mbqOpt['notIncludeTop']) {
                    $search['exclude_sticky'] = true;
                } elseif ($mbqOpt['top']) {
                    $search['sticky_only'] = true;
                } else {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
                }
                $search['sort']['lastcontent'] = 'desc';
                try {
                    $result = vB_Api::instanceInternal('search')->getInitialResults($search, $oMbqDataPage->numPerPage, $oMbqDataPage->curPage, true);
                    if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                        $oMbqDataPage->totalNum = $result['totalRecords'];
                        $arrTopicRecord = $result['results'];
                    } else {
                        $oMbqDataPage->totalNum = 0;
                        $arrTopicRecord = array();
                    }
                } catch (Exception $e) {
                    $oMbqDataPage->totalNum = 0;
                    $arrTopicRecord = array();
                }
                $nodeIds = array();
                foreach ($arrTopicRecord as $topicRecord) {
                    $nodeIds[] = $topicRecord['nodeid'];
                }
                /* common begin */
                $mbqOpt['case'] = 'byTopicIds';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumTopic($nodeIds, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byPmIds') {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
            try {
                $result = vB_Api::instanceInternal('node')->getFullContentforNodes($var);
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                    $arrTopicRecord = $result;
                } else {
                    $arrTopicRecord = array();
                }
            } catch (Exception $e) {
                $arrTopicRecord = array();
            }
            /* common begin */
            $mbqOpt['case'] = 'byArrTopicRecord';
            return $this->getObjsMbqEtForumTopic($arrTopicRecord, $mbqOpt);
            /* common end */
        } elseif ($mbqOpt['case'] == 'byArrPmRecord') {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
            $arrTopicRecord = $var;
            /* common begin */
            $objsMbqEtForumTopic = array();
            $authorUserIds = array();
            $lastReplyUserIds = array();
            $forumIds = array();
            $topicIds = array();
            foreach ($arrTopicRecord as $topicRecord) {
                $objsMbqEtForumTopic[] = $this->initOMbqEtForumTopic($topicRecord, array('case' => 'byTopicRecord'));
            }
            foreach ($objsMbqEtForumTopic as $oMbqEtForumTopic) {
                $authorUserIds[$oMbqEtForumTopic->topicAuthorId->oriValue] = $oMbqEtForumTopic->topicAuthorId->oriValue;
                $lastReplyUserIds[$oMbqEtForumTopic->lastReplyAuthorId->oriValue] = $oMbqEtForumTopic->lastReplyAuthorId->oriValue;
                $forumIds[$oMbqEtForumTopic->forumId->oriValue] = $oMbqEtForumTopic->forumId->oriValue;
                $topicIds[$oMbqEtForumTopic->topicId->oriValue] = $oMbqEtForumTopic->topicId->oriValue;
            }
            /* load oMbqEtForum property */
            $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
            $objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum($forumIds, array('case' => 'byForumIds'));
            foreach ($objsMbqEtForum as $oNewMbqEtForum) {
                foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                    if ($oNewMbqEtForum->forumId->oriValue == $oMbqEtForumTopic->forumId->oriValue) {
                        $oMbqEtForumTopic->oMbqEtForum = $oNewMbqEtForum;
                    }
                }
            }
            /* load topic author */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $objsAuthorMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($authorUserIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                foreach ($objsAuthorMbqEtUser as $oAuthorMbqEtUser) {
                    if ($oMbqEtForumTopic->topicAuthorId->oriValue == $oAuthorMbqEtUser->userId->oriValue) {
                        $oMbqEtForumTopic->oAuthorMbqEtUser = $oAuthorMbqEtUser;
                        if ($oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue()) {
                            $oMbqEtForumTopic->authorIconUrl->setOriValue($oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue);
                        }
                        break;
                    }
                }
            }
            /* load oLastReplyMbqEtUser */
            $objsLastReplyMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($lastReplyUserIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                foreach ($objsLastReplyMbqEtUser as $oLastReplyMbqEtUser) {
                    if ($oMbqEtForumTopic->lastReplyAuthorId->oriValue == $oLastReplyMbqEtUser->userId->oriValue) {
                        $oMbqEtForumTopic->oLastReplyMbqEtUser = $oLastReplyMbqEtUser;
                        break;
                    }
                }
            }
            /* make other properties */
            $oMbqAclEtForumPost = MbqMain::$oClk->newObj('MbqAclEtForumPost');
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                if ($oMbqAclEtForumPost->canAclReplyPost($oMbqEtForumTopic)) {
                    $oMbqEtForumTopic->canReply->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.range.yes'));
                } else {
                    $oMbqEtForumTopic->canReply->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.range.no'));
                }
            }
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $oMbqDataPage->datas = $objsMbqEtForumTopic;
                return $oMbqDataPage;
            } else {
                return $objsMbqEtForumTopic;
            }
            /* common end */
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>