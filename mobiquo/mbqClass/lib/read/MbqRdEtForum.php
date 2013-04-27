<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForum');

/**
 * forum read class
 * 
 * @since  2012-8-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtForum extends MbqBaseRdEtForum {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtForum, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get forum tree structure
     *
     * @return  Array
     */
    public function getForumTree() {
        $arr = vB_Api::instance('node')->fetchChannelNodeTree(0, EXTTMBQ_NO_LIMIT_DEPTH, 1, EXTTMBQ_NO_LIMIT_PERPAGE);
        $channelNodesTree = $arr['channels'];
        $newTree = array();
        foreach ($channelNodesTree as $channelNode) {
            $id = $channelNode['nodeid'];
            if (!in_array($id, MbqMain::$oMbqAppEnv->hideForumIds) && ($oNewMbqEtForum = $this->initOMbqEtForum($channelNode, array('case' => 'channelNode')))) {
                $newTree [$id] = $oNewMbqEtForum;
                $this->exttRecurInitObjsSubMbqEtForum($newTree[$id], $channelNode['subchannels']);
            }
        }
        return $newTree;
    }
    /**
     * recursive init objsSubMbqEtForum
     *
     * @param  Object  $oMbqEtForum  the object need init objsSubMbqEtForum
     * @param  Array 
     */
    private function exttRecurInitObjsSubMbqEtForum(&$oMbqEtForum, $arr) {
        foreach ($arr as $channelNode) {
            $id = $channelNode['nodeid'];
            if (!in_array($id, MbqMain::$oMbqAppEnv->hideForumIds) && ($oNewMbqEtForum = $this->initOMbqEtForum($channelNode, array('case' => 'channelNode')))) {
                $oMbqEtForum->objsSubMbqEtForum[$id] = $oNewMbqEtForum;
                $this->exttRecurInitObjsSubMbqEtForum($oMbqEtForum->objsSubMbqEtForum[$id], $channelNode['subchannels']);
            }
        }
    }
    
    /**
     * get forum objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumIds' means get data by forum ids.$var is the ids.
     * $mbqOpt['case'] = 'subscribed' means get subscribed data.$var is the user id.
     * @return  Array
     */
    public function getObjsMbqEtForum($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byForumIds') {
            $objsMbqEtForum = array();
            $i = 0;
            foreach ($var as $id) {
                if ($oNewMbqEtForum = $this->initOMbqEtForum($id, array('case' => 'byForumId'))) {
                    $objsMbqEtForum[$i] = $oNewMbqEtForum;
                    $i ++;
                }
            }
            return $objsMbqEtForum;
        } elseif ($mbqOpt['case'] == 'subscribed') {
            try {
                $result = vB_Api::instance('follow')->getFollowing(
                    $var,
                    vB_Api_Follow::FOLLOWTYPE_CHANNELS,
                    array(
                        vB_Api_Follow::FOLLOWFILTERTYPE_SORT => vB_Api_Follow::FOLLOWFILTER_SORTALL,
                        vB_Api_Follow::FOLLOWTYPE => vB_Api_Follow::FOLLOWTYPE_CHANNELS,
                        null,
                        array(
                            'perpage' => 10000,
                            'page' => 1,
                        )
                    )
                );
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                    $ids = array();
                    foreach ($result['results'] as $r) {
                        $ids[] = $r['keyval'];
                    }
                } else {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Load subscribed forum failed!');
                }
            } catch (Exception $e) {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . 'Load subscribed forum failed!');
            }
            return $this->getObjsMbqEtForum($ids, array('case' => 'byForumIds'));
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one forum by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumId' means init forum by forum id
     * $mbqOpt['case'] = 'channelNode' means init forum by channelNode
     * @return  Mixed
     */
    public function initOMbqEtForum($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byForumId') {
            /*
            try {
                $channelRecord = vB_Api::instance('content_channel')->fetchChannelById($var);
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($channelRecord)) {
                    
                }
            } catch (Exception $e) {
                MbqMain::$oMbqAppEnv->exttEchoException($e);
            }
            */
            try {
                $channelRecord = vB_Api::instance('content_channel')->fetchChannelById($var);
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($channelRecord)) {
                    $oMbqEtForum = MbqMain::$oClk->newObj('MbqEtForum');
                    $oMbqEtForum->forumId->setOriValue($channelRecord['nodeid']);
                    $oMbqEtForum->forumName->setOriValue($channelRecord['title']);
                    $oMbqEtForum->description->setOriValue($channelRecord['description']);
                    $oMbqEtForum->totalTopicNum->setOriValue($channelRecord['textcount']);
                    $oMbqEtForum->parentId->setOriValue($channelRecord['parentid']);
                    if ($channelRecord['category']) {
                        $oMbqEtForum->subOnly->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.yes'));
                    } else {
                        $oMbqEtForum->subOnly->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.no'));
                    }
                    $oMbqEtForum->mbqBind['channelRecord'] = $channelRecord;
                    $arrNodeRecord = vB_Api::instance('node')->getFullContentforNodes(array($oMbqEtForum->forumId->oriValue));
                    if (!MbqMain::$oMbqAppEnv->exttHasErrors($arrNodeRecord)) {
                        if ($arrNodeRecord) {
                            $oMbqEtForum->mbqBind['channelFullContent'] = $arrNodeRecord[0];
                            $oMbqAclEtForumTopic = MbqMain::$oClk->newObj('MbqAclEtForumTopic');
                            if ($oMbqAclEtForumTopic->canAclNewTopic($oMbqEtForum)) {
                                $oMbqEtForum->canPost->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.range.yes'));
                            } else {
                                $oMbqEtForum->canPost->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.range.no'));
                            }
                            if ($oMbqEtForum->mbqBind['channelFullContent']['content']['createpermissions']['vbforum_attach']) {
                                $oMbqEtForum->canUpload->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.range.yes'));
                            } else {
                                $oMbqEtForum->canUpload->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.range.no'));
                            }
                        }
                    } else {
                        return false;
                    }
                    return $oMbqEtForum;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } elseif ($mbqOpt['case'] == 'channelNode') {
            return $this->initOMbqEtForum($var['nodeid'], array('case' => 'byForumId'));
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>