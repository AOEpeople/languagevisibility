<?php
/**
 * Wrapperclass for the command map in tce_main
 *
 */
class tx_languagevisibility_commandMap{
	private $cmd_map;
	
	public function setMap($map){
		$this->cmdmap = $map;
	}
	
	/**
	 * Return an array of elements with the command, uid, and table
	 *
	 * example:
	 *  getElementsByCommand('delete');
	 * 
	 * returns all elements in the map it the command delete
	 * @param array $commands
	 * @return unknown
	 */
	public function getElementsByCommands($commands){
		$elements = array();
		
		foreach($this->cmdmap as $table => $entry){
			//traverse records in table with commands
			foreach($entry as $uid => $cmds){
				//traverse command for record in table
				foreach($cmds as $cmd => $active){
					if((bool) $active && in_array($cmd,$commands)){
						//create element instance and append it to the result array
				
						$elements[] =	array('cmd' => $cmd, 'uid' => $uid, 'table' => $table);
					}
				}
			}
		}
		
		return $elements;
	}
	
	/**
	 * removes an element from the command map
	 *
	 * @param unknown_type $element
	 */
	public function removeElement($element){
		unset($this->cmdmap[$element['table']][$element['uid']][$element['cmd']]);
	}
	
	public function getMap(){
		return $this->cmdmap;	
	}
}
?>