<?php
if (!defined('VB_ENTRY')) die('Access denied.');

/**
 * for vb 5
 * ExttMbqVbLibraryContentPrivatemessage extended from vB_Library_Content_Privatemessage
 * add method exttMbqGetMessage() modified from method getMessage()
 * add method exttMbqGetMessageTree() modified from method getMessageTree()
 * modify method __construct()
 * 
 * @since  2013-1-7
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqVbLibraryContentPrivatemessage extends vB_Library_Content_Privatemessage
{

	public function __construct()
	{
		parent::__construct();
		$this->pmChannel = $this->nodeApi->fetchPMChannel();
	}
    
	public function exttMbqGetMessage($nodeid)
	{
		$content = $this->nodeApi->getNode($nodeid);
		$userid =  vB::getCurrentSession()->get('userid');
		//return $this->getMessageTree($nodeid, array($userid, $content['userid']), $userid);
		return $this->exttMbqGetMessageTree($nodeid, array($userid, $content['userid']), $userid);
	}
	
	//modify:do not mark read when get message tree
	public function exttMbqGetMessageTree($nodeid, $exclude, $userid)
	{
		//The permissions are checked before we get here, so we don't need to be concerned
		$messagesQry = $this->assertor->getRows('vBForum:getPrivateMessageTree', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_STORED,
			'nodeid' => $nodeid,
			'userid' => $userid
		));

		$messages = array();
		foreach ($messagesQry AS $message)
		{
			if (isset($messages[$message['nodeid']]))
			{
				continue;
			}
			$content = vB_Library::instance('node')->getNodeContent($message['nodeid']);
			$messages[$message['nodeid']] = $message + $content[$message['nodeid']];
		}

		$userApi = vB_Api::instanceInternal('user');
		$initial = key($messages);
		$messageIds = array();
		foreach ($messages as $key => $message)
		{
			// @TODO implement fetchAvatars to get all avatars together instead of one by one
			$messages[$key]['senderAvatar'] = $messages[$key]['avatar'];//$userApi->fetchAvatar($message['userid']);
			$messages[$key]['starter'] = false;

			if (empty($message['pagetext']))
			{
				$messages[$key]['pagetext'] = $message['rawtext'];
			}
			$messageIds[] = $message['nodeid'];
		}
		$messages[$initial]['starter'] = true;

		// try to set the first recipient
		$needLast = array();
		if (empty($messages[$initial]['lastauthorid']) OR $messages[$initial]['lastauthorid'] == $userid)
		{
			$needLast[] = $messages[$initial]['nodeid'];
		}

		// @TODO check for a way to implement a generic protected library method to fetch recipients instead of cloning code through methods.
		// fetch the right lastauthor if needed
		if (!empty($needLast))
		{
			$neededUsernames = $this->assertor->assertQuery('vBForum:getPMLastAuthor', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_STORED, 'nodeid' => $needLast, 'userid' => $userid));
			foreach ($neededUsernames AS $user)
			{
				if ($user['nodeid'] == $messages[$initial]['nodeid'])
				{
					$messages[$initial]['lastcontentauthor'] = $user['username'];
					$messages[$initial]['lastauthorid'] = $user['userid'];
				}
			}
		}

		$included = false;
		$recipients = array();
		$recipientsInfo = $this->assertor->assertQuery('vBForum:getPMRecipientsForMessage', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_STORED,
			'nodeid' => $messages[$initial]['nodeid']
		));

		foreach ($recipientsInfo as $recipient)
		{
			if (($recipient['userid'] == $userid))
			{
				if (!$included)
				{
					$included = true;
				}

				continue;
			}
			else if ($messages[$initial]['lastcontentauthor'] == $recipient['username'])
			{
				continue;
			}

			if (!isset($recipients[$recipient['userid']]))
			{
				$recipients[$recipient['userid']] = $recipient;
			}
		}

		// and set the first recipient properly if needed
		$firstRecipient = array();
		if (!empty($messages[$initial]['lastcontentauthor']) AND !empty($messages[$initial]['lastauthorid']) AND ($messages[$initial]['lastauthorid'] != $userid))
		{
			$firstRecipient = array(
				'userid' => $messages[$initial]['lastauthorid'],
				'username' => $messages[$initial]['lastcontentauthor']
			);
		}
		else if (!empty($recipients))
		{
			$firstRecipient = reset($recipients);
			unset($recipients[$firstRecipient['userid']]);
		}

		//set these messages read.
		//$this->setRead($messageIds, 1, $userid);
		return array('message' => $messages[$initial], 'messages' => $messages, 'otherRecipients' => count($recipients), 'firstRecipient' => $firstRecipient, 'included' => $included);
	}
	
}

?>