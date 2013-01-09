<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActReplyConversation');

/**
 * reply_conversation action
 * 
 * @since  2012-11-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqActReplyConversation extends MbqBaseActReplyConversation {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    public function actionImplement() {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
        } else {
            MbqError::alert('', "Not support module private conversation!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqEtPcMsg = MbqMain::$oClk->newObj('MbqEtPcMsg');
        $oMbqEtPcMsg->convId->setOriValue(MbqMain::$input[0]);
        $oMbqEtPcMsg->msgContent->setOriValue(MbqMain::$input[1]);
        $oMbqEtPcMsg->msgTitle->setOriValue(MbqMain::$input[2]);
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if ($objsMbqEtPc = $oMbqRdEtPc->getObjsMbqEtPc(array($oMbqEtPcMsg->convId->oriValue), array('case' => 'byConvIds'))) {
            $oMbqEtPc = array_shift($objsMbqEtPc);
            $oMbqAclEtPcMsg = MbqMain::$oClk->newObj('MbqAclEtPcMsg');
            if ($oMbqAclEtPcMsg->canAclReplyConversation($oMbqEtPcMsg, $oMbqEtPc)) {
                $oMbqWrEtPcMsg = MbqMain::$oClk->newObj('MbqWrEtPcMsg');
                $oMbqWrEtPcMsg->addMbqEtPcMsg($oMbqEtPcMsg, $oMbqEtPc);
                $this->data['result'] = true;
                $this->data['msg_id'] = (string) $oMbqEtPcMsg->msgId->oriValue;
            } else {
                MbqError::alert('', '', '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
        }
    }
  
}

?>