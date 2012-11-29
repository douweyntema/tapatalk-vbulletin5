<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtUser');

/**
 * user read class
 * 
 * @since  2012-8-6
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtUser extends MbqBaseRdEtUser {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtUser, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get user objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byUserIds' means get data by user ids.$var is the ids.
     * @mbqOpt['case'] = 'online' means get online user.
     * @return  Array
     */
    public function getObjsMbqEtUser($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byUserIds') {
            $arrUserRecord = array();
            foreach ($var as $userId) {
            	try {
                	$result = vB_Api::instanceInternal('user')->fetchProfileInfo($userId);
                	if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                	    $arrUserRecord[] = $result;
                    } else {
                        //
                    }
                } catch (Exception $e) {
                    //
                }
            }
            $objsMbqEtUser = array();
            foreach ($arrUserRecord as $userRecord) {
                $objsMbqEtUser[] = $this->initOMbqEtUser($userRecord, array('case' => 'userRecord'));
            }
            return $objsMbqEtUser;
        } elseif ($mbqOpt['case'] == 'online') {
        	try {
            	$result = vB_Api::instance('wol')->fetchAll();
            	if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
            	    $userIds = array();
                    foreach ($result as $record) {
                        $userIds[] = $record['userid'];
                    }
                    return $this->getObjsMbqEtUser($userIds, array('case' => 'byUserIds'));
                } else {
                    return array();
                }
            } catch (Exception $e) {
                return array();
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one user by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'userRecord' means init user by userRecord.$var is userRecord.
     * $mbqOpt['case'] = 'byUserId' means init user by user id.$var is user id.
     * $mbqOpt['case'] = 'byLoginName' means init user by login name.$var is login name.
     * @return  Mixed
     */
    public function initOMbqEtUser($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'userRecord') {
            $oMbqEtUser = MbqMain::$oClk->newObj('MbqEtUser');
            $oMbqEtUser->userId->setOriValue($var['userid']);
            $oMbqEtUser->loginName->setOriValue($var['username']);
            $oMbqEtUser->userName->setOriValue($var['username']);
            $oMbqEtUser->userGroupIds->setOriValue(array($var['usergroupid']));
            $iconUrl = vB_Api::instanceInternal('user')->fetchAvatar($var['userid'], true);
            $iconUrl = $iconUrl['avatarpath'];
            $iconUrl = vB5_Config::instance()->baseurl_core . '/' . $iconUrl;
            $oMbqEtUser->iconUrl->setOriValue($iconUrl);
            $oMbqEtUser->canSearch->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSearch.range.yes'));
            $oMbqEtUser->postCount->setOriValue($var['posts']);
            $oMbqEtUser->displayText->setOriValue($var['displayusertitle']);
            $oMbqEtUser->regTime->setOriValue($var['joindate']);
            $oMbqEtUser->lastActivityTime->setOriValue($var['lastactivity']);
            $oMbqEtUser->canWhosonline->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canWhosonline.range.yes'));
            $oMbqEtUser->mbqBind['userRecord'] = $var;
            return $oMbqEtUser;
        } elseif ($mbqOpt['case'] == 'byUserId') {
            $userIds = array($var);
            $objsMbqEtUser = $this->getObjsMbqEtUser($userIds, array('case' => 'byUserIds'));
            if (is_array($objsMbqEtUser) && (count($objsMbqEtUser) == 1)) {
                return $objsMbqEtUser[0];
            }
            return false;
        } elseif ($mbqOpt['case'] == 'byLoginName') {
        	try {
            	$result = vB_Api::instanceInternal('user')->fetchByUsername($var);
            	if (!MbqMain::$oMbqAppEnv->exttHasErrors($result)) {
                    return $this->initOMbqEtUser($result['userid'], array('case' => 'byUserId'));
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * get user display name
     *
     * @param  Object  $oMbqEtUser
     * @return  String
     */
    public function getDisplayName($oMbqEtUser) {
        return $oMbqEtUser->loginName->oriValue;
    }
    
    /**
     * login
     *
     * @param  String  $loginName
     * @param  String  $password
     * @return  Boolean  return true when login success.
     */
    public function login($loginName, $password) {
        $oEntryController = new EntryController();
        $oEntryController->Initialize();
        /* modified from EntryController::SignIn() */
        $oEntryController->FireEvent('SignIn');
        //$Email = $this->Form->GetFormValue('Email');
        $Email = $loginName;
        $User = Gdn::UserModel()->GetByEmail($Email);
        if (!$User)
           $User = Gdn::UserModel()->GetByUsername($Email);

        if (!$User) {
           //$this->Form->AddError('ErrorCredentials');
           return false;
        } else {
           //$ClientHour = $this->Form->GetFormValue('ClientHour');
           $ClientHour = date('Y-m-d H:i');
           $HourOffset = Gdn_Format::ToTimestamp($ClientHour) - time();
           $HourOffset = round($HourOffset / 3600);

           // Check the password.
           $PasswordHash = new Gdn_PasswordHash();
           //if ($PasswordHash->CheckPassword($this->Form->GetFormValue('Password'), GetValue('Password', $User), GetValue('HashMethod', $User))) {
           if ($PasswordHash->CheckPassword($password, GetValue('Password', $User), GetValue('HashMethod', $User))) {
              //Gdn::Session()->Start(GetValue('UserID', $User), TRUE, (bool)$this->Form->GetFormValue('RememberMe'));
              Gdn::Session()->Start(GetValue('UserID', $User), TRUE, TRUE);
              if (!Gdn::Session()->CheckPermission('Garden.SignIn.Allow')) {
                 //$this->Form->AddError('ErrorPermission');
                 Gdn::Session()->End();
                 return false;
              } else {
                 if ($HourOffset != Gdn::Session()->User->HourOffset) {
                    Gdn::UserModel()->SetProperty(Gdn::Session()->UserID, 'HourOffset', $HourOffset);
                 }
                 MbqMain::$oMbqAppEnv->oCurStdUser = $User;

                 //$this->_SetRedirect();
                 $this->initOCurMbqEtUser();
                 return true;
              }
           } else {
              //$this->Form->AddError('ErrorCredentials');
              return false;
           }
        }
    }
    
    /**
     * logout
     *
     * @return  Boolean  return true when logout success.
     */
    public function logout() {
        $oEntryController = new EntryController();
        $oEntryController->Initialize();
        /* modified from EntryController::SignOut() */
        if (MbqMain::hasLogin()) {
             $User = Gdn::Session()->User;
             
             $oEntryController->EventArguments['SignoutUser'] = $User;
             $oEntryController->FireEvent("BeforeSignOut");
             
             // Sign the user right out.
             Gdn::Session()->End();
             
             $oEntryController->EventArguments['SignoutUser'] = $User;
             $oEntryController->FireEvent("SignOut");
        }
        $oEntryController->Leaving = FALSE;
        return true;
    }
    
    /**
     * init current user obj if login
     */
    public function initOCurMbqEtUser() {
        if (MbqMain::$oMbqAppEnv->oCurStdUser) {
            MbqMain::$oCurMbqEtUser = $this->initOMbqEtUser(MbqMain::$oMbqAppEnv->oCurStdUser, array('case' => 'oStdUser'));
        }
    }
  
}

?>