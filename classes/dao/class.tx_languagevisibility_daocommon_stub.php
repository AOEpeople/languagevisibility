<?php

/** used to test the objects which needs simple database access:
 just replace the real daocommon with this stub.

 Then set the expected results before passing this stub dao to the object you want to test

**/

class tx_languagevisibility_daocommon_stub {
	var $row;

	function stub_setRow($row,$table) {
		$this->row[$table][$row['uid']]=$row;
	}
	function getRecord($uid,$table) {

    return $this->row[$table][$uid];

	}


}
1578
?>