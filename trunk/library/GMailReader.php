<?php 
/*
GmailReader  by Jérome  DEBRAY
Copyright - 2010
Copyright (C) <2010>  <Jérome DEBRAY>
GmailReader.php is a php class to get gmail mail by imap protocol

contact : ornitho13@gmail.com
Program : GmailReader PHP Class
    
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

	If you need the source, you could contact me at ornitho13@gmail.com


*/
class GmailReader{
	
	private $mail = null;
	
	public function __construct()
	{
		
	}
	
	public function openMailBox($user, $password)
	{
		$this->mail = imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $user, $password);
	}
	
	public function closeMailBox()
	{
		imap_close($this->mail);
	}
	
	public function getMailBoxDirectory()
	{
		return imap_list($this->mail, "{imap.gmail.com:993/imap/ssl}", "*");
	}
	
	public function getCleanFolder($folder)
	{
		return str_replace('{imap.gmail.com:993/imap/ssl}', '', $folder);
	}
	
	public function getNumMessages()
	{
		return intval(imap_num_msg($this->mail));
	}
	
	public function getNumUnseenMessages()
	{
		return intval(imap_search($this->mail, 'UNSEEN'));
	}
	
	public function getNumNewMessages()
	{
		return intval(imap_search($this->mail, 'NEW'));
	}
	
	public function getAllMessages()
	{
		$mailsId = imap_search($this->mail, 'ALL');
		return $this->_getMessages($mailsId);
	}
	
	public function getAnsweredMessages()
	{
		$mailsId = imap_search($this->mail, 'ANSWERED');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesWithBcc($bcc)
	{
		$mailsId = imap_search($this->mail, 'BCC "' . $bcc . '"');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesBefore($date)
	{
		$mailsId = imap_search($this->mail, 'BEFORE "'.$date.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesWithBody($body)
	{
		$mailsId = imap_search($this->mail, 'BODY "'.$body.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesWithCc($cc)
	{
		$mailsId = imap_search($this->mail, 'CC "'.$cc.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getDeletedMessages()
	{
		$mailsId = imap_search($this->mail, 'DELETED');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesFrom($from)
	{
		$mailsId = imap_search($this->mail, 'FROM "'.$from.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getNewMessages()
	{
		$mailsId = imap_search($this->mail, 'NEW');
		return $this->_getMessages($mailsId);	
	}
	
	public function getMessagesOnDate($date)
	{
		$mailsId = imap_search($this->mail, 'ON "'.$date.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesWithSubject($subject)
	{
		$mailsId = imap_search($this->mail, 'SUBJECT "'.$subject.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getMessagesTo($to)
	{
		$mailsId = imap_search($this->mail, 'TO "'.$to.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getSeenMessages()
	{
		$mailsId = imap_search($this->mail, 'SEEN');
		return $this->_getMessages($mailsId);
	}
	
	public function getUnansweredMessages()
	{
		$mailsId = imap_search($this->mail, 'UNANSWERED');
		return $this->_getMessages($mailsId);	
	}
	
	public function getMessagesSince($date)
	{
		$mailsId = imap_search($this->mail, 'SINCE "'.$date.'"');
		return $this->_getMessages($mailsId);
	}
	
	public function getUnreadMessages()
	{
		$mailsId = imap_search($this->mail, 'UNSEEN');
		return $this->_getMessages($mailsId);
	}
	
	public function getBodyMessage($id)
	{
		return imap_body($this->mail, intval($id));
	}
	
	private function _getMessages($mailsId){
		$i = 0;
		foreach($mailsId as $mailId){
			$results[$i] = imap_header($this->mail, $mailId);
			$i++;
		}
		return $results;
	}
}