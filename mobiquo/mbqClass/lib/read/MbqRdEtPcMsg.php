<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtPcMsg');

/**
 * private conversation message read class
 * 
 * @since  2012-11-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtPcMsg extends MbqBaseRdEtPcMsg {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtPcMsg, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get private conversation message objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byPc' means get data by private conversation obj.$var is the private conversation obj
     * $mbqOpt['case'] = 'byMsgIds' means get data by conversation message ids.$var is the ids.
     * @return  Mixed
     */
    public function getObjsMbqEtPcMsg($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byPc') {
            $oMbqEtPc = $var;
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                try {
                    $resultThread = vB_Api::instanceInternal('content_privatemessage')->getMessage($oMbqEtPc->convId->oriValue); //can cause mark read
                    if (!MbqMain::$oMbqAppEnv->exttHasErrors($resultThread)) {
                        $msgIds = array();
                        foreach ($resultThread['messages'] as $msg) {
                            $msgIds[$msg['nodeid']] = $msg['nodeid'];
                        }
                    } else {
                        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . "Can not get conversation thread.");
                    }
                } catch (Exception $e) {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . "Can not get conversation thread.");
                }
                $oMbqDataPage->totalNum = count($msgIds);
                $nodeIds = array();
                $i = 1;
                foreach ($msgIds as $nodeId) {
                    if (($i >= $oMbqDataPage->startNum) && ($i <= ($oMbqDataPage->startNum + $oMbqDataPage->numPerPage - 1))) {
                        $nodeIds[] = $nodeId;
                    }
                    $i ++;
                }
                /* common begin */
                $mbqOpt['case'] = 'byMsgIds';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtPcMsg($nodeIds, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byMsgIds') {
            $objsMbqEtPcMsg = array();
            $authorUserIds = array();
            try {
                $result = vB_Api::instanceInternal('node')->getFullContentforNodes($var);
                if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                    $arrMsgRecord = $result;
                    require_once(MBQ_APPEXTENTION_PATH.'ExttMbqVbLibraryContentPrivatemessage.php');
                    $oExttMbqVbLibraryContentPrivatemessage = new ExttMbqVbLibraryContentPrivatemessage();
                    foreach ($arrMsgRecord as $msgRecord) {
                        $nodeid = $msgRecord['content']['nodeid'];
                        $resultText = vB_Api::instanceInternal('content_text')->getDataForParse(array($nodeid));
                        $macro = vB5_Template_NodeText::instance()->register($nodeid, $resultText[$nodeid]['bbcodeoptions']);
                        vB5_Template_NodeText::instance()->replacePlaceholders($macro);
                        $oMbqEtPcMsg = MbqMain::$oClk->newObj('MbqEtPcMsg');
                        $oMbqEtPcMsg->mbqBind['msgRecord'] = $msgRecord;
                        $oMbqEtPcMsg->mbqBind['bbcodeoptions'] = $resultText[$nodeid]['bbcodeoptions'];
                        $oMbqEtPcMsg->msgId->setOriValue($msgRecord['content']['nodeid']);
                        $oMbqEtPcMsg->convId->setOriValue($msgRecord['content']['starter']);
                        $oMbqEtPcMsg->msgTitle->setOriValue($msgRecord['content']['title']);
                        $oMbqEtPcMsg->msgContent->setOriValue($msgRecord['content']['rawtext']);
                        $oMbqEtPcMsg->msgContent->setAppDisplayValue($macro);
                        $oMbqEtPcMsg->msgContent->setTmlDisplayValue($this->processPcMsgContentForDisplay($macro, true, $oMbqEtPcMsg));
                        $oMbqEtPcMsg->msgContent->setTmlDisplayValueNoHtml($this->processPcMsgContentForDisplay($macro, false, $oMbqEtPcMsg));
                        $oMbqEtPcMsg->msgAuthorId->setOriValue($msgRecord['content']['userid']);
                        $authorUserIds[$msgRecord['content']['userid']] = $msgRecord['content']['userid'];
                        $oMbqEtPcMsg->postTime->setOriValue($msgRecord['content']['created']);
                        $objsMbqEtPcMsg[$oMbqEtPcMsg->msgId->oriValue] = $oMbqEtPcMsg;
                    }
                } else {
                    MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . "Can not get conversation message records.");
                }
            }  catch (Exception $e) {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . "Can not get conversation message records.");
            }
            /* load oAuthorMbqEtUser property */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $objsAuthorMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($authorUserIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtPcMsg as &$oMbqEtPcMsg) {
                foreach ($objsAuthorMbqEtUser as $oAuthorMbqEtUser) {
                    if ($oMbqEtPcMsg->msgAuthorId->oriValue == $oAuthorMbqEtUser->userId->oriValue) {
                        $oMbqEtPcMsg->oAuthorMbqEtUser = $oAuthorMbqEtUser;
                        break;
                    }
                }
            }
            ksort($objsMbqEtPcMsg);
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $oMbqDataPage->datas = $objsMbqEtPcMsg;
                return $oMbqDataPage;
            } else {
                return $objsMbqEtPcMsg;
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * process content for display in mobile app
     *
     * @params  String  $content
     * @params  Boolean  $returnHtml
     * @params  Object  $oMbqEtPcMsg
     * @return  String
     */
    public function processPcMsgContentForDisplay($content, $returnHtml, $oMbqEtPcMsg) {
        $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
        return $oMbqRdEtForumPost->processContentForDisplay($content, $returnHtml, $oMbqEtPcMsg);
    }
  
}

?>