<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtPcMsg');

/**
 * private conversation message write class
 * 
 * @since  2012-11-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtPcMsg extends MbqBaseWrEtPcMsg {
    
    public function __construct() {
    }
    
    /**
     * add private conversation message
     *
     * @param  Object  $oMbqEtPcMsg
     * @param  Object  $oMbqEtPc
     */
    public function addMbqEtPcMsg(&$oMbqEtPcMsg, $oMbqEtPc) {
        try {
            $result = vB_Api::instanceInternal('content_privatemessage')->add(
                array(
            		'respondto' => $oMbqEtPc->convId->oriValue,
            		'rawtext' => $oMbqEtPcMsg->msgContent->oriValue,
            		'msgtype' => 'message'
                )
            );
            if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                $oMbqEtPcMsg->msgId->setOriValue($result);
            } else {
                MbqError::alert('', "Can not save!", '', MBQ_ERR_APP);
            }
        } catch (Exception $e) {
            MbqError::alert('', "Can not save!", '', MBQ_ERR_APP);
        }
    }
  
}

?>