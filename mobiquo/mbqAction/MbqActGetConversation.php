<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActGetConversation');

/**
 * get_conversation action
 * 
 * @since  2012-11-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqActGetConversation extends MbqBaseActGetConversation {
    
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
        $convId = MbqMain::$input[0];
        $startNum = (int) MbqMain::$input[1];
        $lastNum = (int) MbqMain::$input[2];
        $returnHtml = (boolean) MbqMain::$input[3];
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if ($objsMbqEtPc = $oMbqRdEtPc->getObjsMbqEtPc(array($convId), array('case' => 'byConvIds'))) {
            $oMbqEtPc = array_shift($objsMbqEtPc);
            $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
            if ($oMbqAclEtPc->canAclGetConversation($oMbqEtPc)) {    //acl judge
                $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                $oMbqDataPage = $oMbqRdEtPcMsg->getObjsMbqEtPcMsg($oMbqEtPc, array('case' => 'byPc', 'oMbqDataPage' => $oMbqDataPage));
                $this->data = $oMbqRdEtPc->returnApiDataPc($oMbqEtPc);
                $this->data['list'] = $oMbqRdEtPcMsg->returnApiArrDataPcMsg($oMbqDataPage->datas, $returnHtml);
            } else {
                MbqError::alert('', '', '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid conversation id!", '', MBQ_ERR_APP);
        }
    }
  
}

?>