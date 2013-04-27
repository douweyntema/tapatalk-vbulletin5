<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtAtt');

/**
 * attachment write class
 * 
 * @since  2012-9-11
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtAtt extends MbqBaseWrEtAtt {
    
    public function __construct() {
    }
    
    /**
     * upload an attachment
     *
     * @param  Integer  $forumId
     * @param  String  $groupId
     * @return  Object  $oMbqEtAtt
     */
    public function uploadAttachment($forumId, $groupId) {
    	foreach($_FILES['attachment'] as $k => $v){
    		if(is_array($_FILES['attachment'][$k]))
    			$_FILES['attachment'][$k] = $_FILES['attachment'][$k][0];
    	}
    	try {
        	$result = vB_Api::instance('content_attach')->upload($_FILES['attachment']);
        	if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
        	    /*
        	        since vb5 get db errors when calling the following code:
        	        vB_Api::instance('content_attach')->fetchAttachByFiledataids(array($result['filedataid']));
                */
                //so we only use very simple data to return
                $oMbqEtAtt = MbqMain::$oClk->newObj('MbqEtAtt');
                //the attId is conflicting with the MbqRdEtAtt::initOMbqEtAtt(),but we maybe must use it to make the attachment bbcode on app!!!
                $oMbqEtAtt->attId->setOriValue($result['filedataid']);
                $oMbqEtAtt->filtersSize->setOriValue($result['filesize']);
                $oMbqEtAtt->uploadFileName->setOriValue($result['filename']);
                return $oMbqEtAtt;
            } else {
            	MbqError::alert('', "Upload attachment failed!", '', MBQ_ERR_APP);
            }
        } catch (Exception $e) {
        	MbqError::alert('', "Upload attachment failed!", '', MBQ_ERR_APP);
        }
    }
  
}

?>