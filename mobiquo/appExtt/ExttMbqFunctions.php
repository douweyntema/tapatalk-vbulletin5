<?php
if (!defined('VB_ENTRY')) die('Access denied.');

/**
 * here is some functions needed by plugin
 * 
 * @since  2013-1-28
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqFunctions
{

	/**
	 * Fetch who is online records
	 *
	 * modified from vB_Api_Wol::fetchAll() in vb5 beta26
	 */
	public function exttMbqFetchAll($pagekey = '', $who = '', $pagenumber = 1, $perpage = 0, $sortfield = 'username', $sortorder = 'asc', $resolveIp = false)
	{
		$currentUserContext = vB::getUserContext();
		if (!$currentUserContext->hasPermission('wolpermissions', 'canwhosonline'))
		{
			throw new vB_Exception_Api('no_permission');
		}

		$vboptions = vB::getDatastore()->getValue('options');
		$bf_misc_useroptions = vB::getDatastore()->getValue('bf_misc_useroptions');
		// check permissions
		$canSeeIp = $currentUserContext->hasPermission('wolpermissions', 'canwhosonlineip');
		$canViewFull = $currentUserContext->hasPermission('wolpermissions', 'canwhosonlinefull');
		$canViewBad = $currentUserContext->hasPermission('wolpermissions', 'canwhosonlinebad');
		$canViewlocationUser = $currentUserContext->hasPermission('wolpermissions', 'canwhosonlinelocation');

		$data = array(
			'who' => $who,
			'pagenumber' => $pagenumber,
			vB_dB_Query::PARAM_LIMIT => $perpage,
			'sortfield' => $sortfield,
			'sortorder' => $sortorder,
		);

		if ($pagekey)
		{
			$data['pagekey'] = $pagekey;
		}

		$allusers = vB::getDbAssertor()->assertQuery('fetchWolAllUsers', $data);

		$onlineUsers = array();
		$onlineGuests = array();
		foreach ($allusers as $userRecord)
		{
			$usergroupidAux = $userRecord['usergroupid'];
			$userRecord = array_merge($userRecord, convert_bits_to_array($userRecord['options'] , $bf_misc_useroptions));
			$resolved = false;
			
			if ($userRecord['invisible'])
			{
				if (!($currentUserContext->hasPermission('genericpermissions', 'canseehidden') OR $userRecord['userid'] == vB::getCurrentSession()->fetch_userinfo_value('userid')))
				{
					continue;
				}
			}

			if (($userRecord['userid'] > 0 AND (empty($onlineUsers[$userRecord['userid']]) OR $onlineUsers[$userRecord['userid']]['rawlastactivity'] < $userRecord['lastactivity']))
				OR $userRecord['userid'] == 0)
			{

				//We only want the most recent record
				if (($userRecord['userid'] > 0) AND isset($onlineUsers[$userRecord['userid']]))
				{
					continue;
				}

				if ($canViewFull)
				{
					$user = $userRecord;
					$user['usergroupid'] = $usergroupidAux;
					$user['musername'] = vB_Api::instanceInternal("user")->fetchMusername($user);

					$user['rawlastactivity'] = $user['lastactivity'];
					$user['lastactivity'] = vbdate($vboptions['dateformat'] . ' ' . $vboptions['timeformat'], $user['lastactivity']);

					if (isset($user['wol']))
					{
						$user['wol'] = @unserialize($user['wol']);
					}

					$avatar = vB_Api::instanceInternal('user')->fetchAvatar($user['userid']);
					$user['avatarpath'] = $avatar['avatarpath'];
				}
				else
				{
					$user = array(
						'username' => $userRecord['username'],
						'userid' => $userRecord['userid'],
					);

					if ($canSeeIp)
					{
						$user['host'] = $userRecord['host'];
					}

					if ($canViewBad)
					{
						$user['bad'] =  $userRecord['bad'];
						$user['wol'] = unserialize($userRecord['wol']);
					}
					else if (isset($userRecord['wol']))
					{
						$wol =  @unserialize($userRecord['wol']);

						if (!empty($wol['action']))
						{
							$user['wol']['action'] = $wol['action'];
							$user['wol']['action'] = $wol['action'];
						}
					}
				}

				if (!$canViewlocationUser)
				{
					unset($user['location']);
				}

				if (!$user['username'])
				{
					$phrase = vB_Api::instanceInternal('phrase')->fetch('guest');
					$user['username'] = $phrase['guest'];
				}

				if ($resolveIp AND $canSeeIp)
				{
					$user['host'] = @gethostbyaddr($user['host']);
				}

				//$user['reputationimg'] = vB_Library::instance('reputation')->fetchReputationImageInfo($userRecord);   //this can cause exception throwing when $userRecord['userid'] == 0

				$resolved = true;
//				$this->updateWolParams($loggedin[$user['userid']]);
			}

			if ($user['userid'] == 0)
			{
				$onlineGuests[] = $user;
			}
			else if ($resolved)
			{
				$onlineUsers[$user['userid']] = $user;
			}
		}

		return array_merge($onlineUsers, $onlineGuests);
	}
	
}

?>