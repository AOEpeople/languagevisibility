<?php
class tx_languagevisibility_beUser{
	private $be_user;
	
	public function __construct(){
		global $BE_USER;
		
		$this->be_user = $BE_USER;	
	}
	
	/**
	 * This Method determines if the option allow_movecutdelete_foroverlays has been
	 * set. It 
	 *
	 * @return unknown
	 */
	function allowCutCopyMoveDelete(){
		$res = false;	
		if(is_array($this->be_user->userGroups)){
			foreach($this->be_user->userGroups as $group){
				if($group['tx_languagevisibility_allow_movecutdelete_foroverlays']){

					$res = true;
				}
			}
		}
		
		return $res;
	}
	
	
	public function isAdmin(){
		return $this->be_user->isAdmin();
	}
	
	/**
	 * This method returns the userId of the current backend user.
	 * 
	 * @return int userId of the backend user
	 */
	public function getUid(){
		return $this->be_user->user[uid];
	}
	
}
?>