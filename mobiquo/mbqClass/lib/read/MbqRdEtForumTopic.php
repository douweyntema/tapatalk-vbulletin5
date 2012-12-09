<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForumTopic');

/**
 * forum topic read class
 * 
 * @since  2012-8-8
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtForumTopic extends MbqBaseRdEtForumTopic {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtForumTopic, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get forum topic objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForum' means get data by forum obj.$var is the forum obj.
     * $mbqOpt['case'] = 'byArrTopicRecord' means get data by arrTopicRecord.$var is the arrTopicRecord.
     * $mbqOpt['case'] = 'byTopicIds' means get data by topic ids.$var is the ids.
     * $mbqOpt['case'] = 'byAuthor' means get data by author.$var is the MbqEtUser obj.
     * $mbqOpt['top'] = true means get sticky data.
     * $mbqOpt['notIncludeTop'] = true means get not sticky data.
     * @return  Mixed
     */
    public function getObjsMbqEtForumTopic($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byForum') {
            $oMbqEtForum = $var;
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            	$search = array("channel" => $var->forumId->oriValue);
            	$search['view'] = vB_Api_Search::FILTER_VIEW_TOPIC;
            	$search['depth'] = 1;
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
        } elseif ($mbqOpt['case'] == 'byAuthor') {
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
            	$search['authorid'] = $var->userId->oriValue;
            	$search['view'] = vB_Api_Search::FILTER_VIEW_TOPIC;
            	$search['depth'] = EXTTMBQ_NO_LIMIT_DEPTH;
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
        } elseif ($mbqOpt['case'] == 'byTopicIds') {
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
        } elseif ($mbqOpt['case'] == 'byArrTopicRecord') {
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
            //...
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
    
    /**
     * init one forum topic by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byTopicRecord' means init forum topic by topicRecord
     * $mbqOpt['case'] = 'byTopicId' means init forum topic by topic id
     * @return  Mixed
     */
    public function initOMbqEtForumTopic($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byTopicRecord') {
            $oMbqEtForumTopic = MbqMain::$oClk->newObj('MbqEtForumTopic');
            $oMbqEtForumTopic->totalPostNum->setOriValue($var['content']['startertotalcount']);
            $oMbqEtForumTopic->topicId->setOriValue($var['content']['nodeid']);
            $oMbqEtForumTopic->forumId->setOriValue($var['content']['parentid']);
            $oMbqEtForumTopic->topicTitle->setOriValue($var['content']['title']);
            $oMbqEtForumTopic->topicContent->setOriValue($var['content']['rawtext']);
            $oMbqEtForumTopic->shortContent->setOriValue(MbqMain::$oMbqCm->getShortContent($var['content']['rawtext']));
            $oMbqEtForumTopic->topicAuthorId->setOriValue($var['content']['starteruserid']);
            $oMbqEtForumTopic->lastReplyAuthorId->setOriValue($var['content']['lastauthorid'] ? $var['content']['lastauthorid'] : $var['content']['starteruserid']);
            $oMbqEtForumTopic->postTime->setOriValue($var['content']['created']);
            $oMbqEtForumTopic->lastReplyTime->setOriValue($var['content']['lastcontent'] ? $var['content']['lastcontent'] : $var['content']['created']);
            $oMbqEtForumTopic->replyNumber->setOriValue($var['content']['startertotalcount'] - 1);
            $oMbqEtForumTopic->viewNumber->setOriValue(0);
            $oMbqEtForumTopic->mbqBind['topicRecord'] = $var;
            return $oMbqEtForumTopic;
        } elseif ($mbqOpt['case'] == 'byTopicId') {
            $topicId = $var;
            if ($objsMbqEtForumTopic = $this->getObjsMbqEtForumTopic(array($topicId), array('case' => 'byTopicIds'))) {
                return $objsMbqEtForumTopic[0];
            }
            return false;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>