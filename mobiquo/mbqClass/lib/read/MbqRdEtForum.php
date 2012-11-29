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
            if ($oNewMbqEtForum = $this->initOMbqEtForum($channelNode, array('case' => 'channelNode'))) {
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
            if ($oNewMbqEtForum = $this->initOMbqEtForum($channelNode, array('case' => 'channelNode'))) {
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
                    if ($channelRecord['options']['cancontainthreads']) {
                        $oMbqEtForum->subOnly->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.no'));
                    } else {
                        $oMbqEtForum->subOnly->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.yes'));
                    }
                    $oMbqEtForum->mbqBind['channelRecord'] = $channelRecord;
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