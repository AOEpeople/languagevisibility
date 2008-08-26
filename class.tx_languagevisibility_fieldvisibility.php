<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_languagerepository.php');
require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_elementFactory.php');
require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_visibilityService.php');
require_once(t3lib_extMgm::extPath("languagevisibility").'classes/dao/class.tx_languagevisibility_daocommon.php');
require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');

class user_tx_languagevisibility_fieldvisibility {
	private $isNewElement=false;
	private $pageId=0;
	private $modTSconfig=array();

	function init() {
		$this->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageInfoArr);

	}

	public function user_fieldvisibility($PA, $fobj)    {


		$this->init();

		$this->pageId=$PA['row']['pid'];
		$uid=$PA['row']['uid'];
		if (substr($uid,0,3)=='NEW') {
			$this->isNewElement=TRUE;
		}
		if ($PA['table']=='pages' && !$this->isNewElement) {
			$this->pageId=$PA['row']['uid'];
		}
		$_modTSconfig = $GLOBALS["BE_USER"]->getTSConfig('mod.languagevisibility',t3lib_BEfunc::getPagesTSconfig($this->pageId));
		$this->modTSconfig=$_modTSconfig['properties'];

		$value=$PA['row'][$PA['field']];
		if ($PA['row']['l18n_parent'] !=0) {
			return 'this is an overlayelement: Visibility is controled in the default language!';
		}

		$visivilitySetting=@unserialize($value);
		//print_r($visivilitySetting);
		if (!is_array($visivilitySetting) && $value!='') {
			$content.='Visibility Settings seems to be corrupt:'.$value;
		}

		//print_r($PA);
		$dao=t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactoryName= t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
		$elementfactory=new $elementfactoryName($dao);

		try {
			$element=$elementfactory->getElementForTable($PA['table'],$uid);
		  }
		  catch (Exception $e) {
		  	return 'sorry this element supports no visibility settings';
		  }



	    $content.=$element->getInformativeDescription();

	    if($element->isMonolithicTranslated()) {
	    	return $content;
	    }

	    $languageRep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
	    $languageList=$languageRep->getLanguages();

	    $visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');
	    $infosStruct=array();
	    foreach ($languageList as $language) {

	    	$infoitem=array('visible'=>$visibility->isVisible($language,$element),
	    								'languageTitle'=>$language->getTitle($this->pageId),
	    								'languageFlag'=>$language->getFlagImg($this->pageId),
	    								'hasTranslation'=>$element->hasTranslation($language->getUid()),
	    								'isVisible'=>$visibility->isVisible($language,$element)
	    								);
	    	//if there is no access to language - and localsettings exist, then do not show select box
	    	//this is to not be able as an translator to override languagesetting
	    	$currentSetting=$element->getLocalVisibilitySetting($language->getUid());
	    	$currentOptionsForUserAndLanguage=tx_languagevisibility_beservices::getAvailableOptionsForLanguage($language);
	    	if ($currentSetting=='' || isset($currentOptionsForUserAndLanguage[$currentSetting])) {
	    	//if ($currentSetting=='' || $GLOBALS['BE_USER']->checkLanguageAccess($language->getUid())) {
	    		$defaultSelect=$element->getLocalVisibilitySetting($language->getUid());
	    		if ($this->isNewElement && $defaultSelect=='') {
	    			if ($this->modTSconfig['language.'][$language->getUid().'.']['defaultVisibilityOnCreate']!='') {
	    				$defaultSelect=$this->modTSconfig['language.'][$language->getUid().'.']['defaultVisibilityOnCreate'];
	    			}
	    		}
	    		$infoitem['options']=$this->_getSelectBox($language->getUid(),
	    																							$this->_getSelectOptionsForLanguage($language),
	    																							$defaultSelect,
	    																							$PA['itemFormElName']);
	    	}
	    	else {
	    		$infoitem['options']='<input type="hidden" name="'.$PA['itemFormElName'].'['.$language->getUid().']" value="'.$currentSetting.'" ></input>('.$currentSetting.')';
	    	}

	    	$infosStruct[]=$infoitem;
    }
  	$content.=$this->_renderLanguageInfos($infosStruct);

		/*
		$content.=$PA['row']['table'];
		$content.='<hr>';
		$content.=$PA['itemFormElValue'];
		$content.='<hr>';
		$content.=$value;
		$content.='<hr>';
		*/



		$languagerepository= t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languagerepository->getLanguages();

		//typo3conf/ext/templavoila/mod1/db_new_content_el.php?id=6&parentRecord=pages%3A6%3AsDEF%3AlDEF%3Afield_content%3AvDEF%3A2
    //$content.= '<input type="hidden" id="sortingorderinput"   name="'.$PA['itemFormElName'].'"    value="'.htmlspecialchars($PA['itemFormElValue']).'" onchange="'.htmlspecialchars(implode('',$PA['fieldChangeFunc'])).'"  '.$PA['onFocus'].'>';

		return '<div id="fieldvisibility">'.$content.'<a href="#" onclick="resetSelectboxes()">reset</a></div>'.$this->_javascript();
	}

	function _getSelectOptionsForLanguage($language) {
		return tx_languagevisibility_beservices::getAvailableOptionsForLanguage($language);
	}

	function _getSelectBox($languageid,$select,$current,$name) {
		if (count($select)==1)
			$addClassName=' oneitem';
		$content.='<select class="fieldvisibility_selects'.$addClassName.'" name="'.$name.'['.$languageid.']">';
		foreach ($select as $skey=>$svalue) {
			if ($current==$skey) {
					$selected=' selected="selected"';
			}
			else {
				$selected='';
			}
			$content.='<option class="'.$skey.'" value="'.$skey.'"'.$selected.'>'.$svalue.'</option>';
		}
		$content.='</select>';
		return $content;

	}

	function _renderLanguageInfos($infosStruct) {
		$content='<style type="text/css">
		.visibilitytable  {margin: 10px 0 0 0}
		.visibilitytable  .bgColor4 {background-color: #C9B88B}
		.visibilitytable  .bgColor {background-color: #FFEED4}
		.visibilitytable  .lastcell {background-color: #DEEAB5}
		.visibilitytable  .bgColor .lastcell {background-color: #E8EAB5}
		.visibilitytable  .bgColor4 .lastcell {border-bottom: 1px solid #333333; background-color: #C9B88B}
		.visibilitytable th {padding: 2px 5px 2px 2px; text-align: left}
		.visibilitytable select {width: 100px}
		.visibilitytable select.oneitem {background-color: #999999}
		.visibilitytable select option {background-color: #83FF73}
		.visibilitytable select .yes {background-color: #E0FF81}
		.visibilitytable select .no {background-color: #FFCE81}
		.visibilitytable select .t {background-color: #BFFFB7}
		.visibilitytable select .f {background-color: #BFFFB7}
		.visibilitytable td {padding: 0 5px 2px 2px}
		</'.'style>';
		$content.='<table style="border-collapse: collapse;" class="visibilitytable">';
		$content.='<tr class="bgColor4"><th >'.$this->getLLL('language').'</th><th >'.$this->getLLL('visibility').'</th><th>'.$this->getLLL('hastranslation').'</th><th class="lastcell">'.$this->getLLL('isshown').'</th></tr>';
		foreach ($infosStruct as $info) {
			$i++;
			if ($i%2)
				$class=' class="bgColor"';
			else
				$class='';
			$content.='<tr'.$class.'><td>'.$info['languageFlag'].$info['languageTitle'].'</td><td>'.$info['options'].'</td><td style="text-align: center">'.$this->_getStatusImage($info['hasTranslation']).'</td><td style="text-align: center"  class="lastcell">'.$this->_getStatusImage($info['isVisible']).'</td></tr>';
		}
		$content.='</table>';
		return $content;
	}

	function getLLL($key) {
		return $GLOBALS['LANG']->sl('LLL:EXT:languagevisibility/locallang_db.xml:'.$key);
	}
	function _getStatusImage($stat) {
		if ($stat) {
			return '<img src="../typo3conf/ext/languagevisibility/res/ok.gif">';
		}
		else {
			return '<img src="../typo3conf/ext/languagevisibility/res/nok.gif">';
		}
	}
	function _link_edit($table, $id)	{
						global $BACK_PATH;
							$params = '&table='.$table.'&edit['.$table.']['.$id.']=edit';
							//$retUrl = 'returnUrl='.($requestUri==-1?"'+T3_THIS_LOCATION+'":rawurlencode($requestUri?$requestUri:t3lib_div::getIndpEnv('REQUEST_URI')));
							$url= $BACK_PATH."alt_doc.php?id=".$id.$params;
						return '<a href="'.$url.'" target="blank">[edit]</a>';
	}



	/*******************************************
	 *
	 * Link functions (protected)
	 *
	 *******************************************/

	/**
	 * Returns an HTML link for editing
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be edited
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_edit($label, $table, $uid, $forced=FALSE)	{
		if ($label) {
			if (($table == 'pages' && ($this->calcPerms & 2) ||
				$table != 'pages' && ($this->calcPerms & 16)) )	{

						$params = '&edit['.$table.']['.$uid.']=edit';
						$retUrl = 'returnUrl='.($requestUri==-1?"'+T3_THIS_LOCATION+'":rawurlencode($requestUri?$requestUri:t3lib_div::getIndpEnv('REQUEST_URI')));
						$url= "alt_doc.php?".$retUrl.$params;
						$onClick="window.open('".$url."','editpopup','scrollbars=no,status=no,toolbar=no,location=no,directories=no,resizable=no,menubar=no,width=700,height=500,top=10,left=10')";
						return '<a style="text-decoration: none;" href="#" onclick="'.htmlspecialchars($onClick).'">'.$label.'</a>';

				} else {
					return $label;
				}
		}
		return '';
	}

	function _javascript() {


		return '
<script type="text/javascript">

function resetSelectboxes() {
	var obj=getElementsByClassName("fieldvisibility_selects");
	for(i=0;i<obj.length;i++)
  {
    obj[i].selectedIndex=0;
  }
}

function getElementsByClassName(class_name)
{
  var all_obj,ret_obj=new Array(),j=0,teststr;

  if(document.all)all_obj=document.all;
  else if(document.getElementsByTagName && !document.all)
    all_obj=document.getElementsByTagName("*");

  for(i=0;i<all_obj.length;i++)
  {
    if(all_obj[i].className.indexOf(class_name)!=-1)
    {
      teststr=","+all_obj[i].className.split(" ").join(",")+",";
      if(teststr.indexOf(","+class_name+",")!=-1)
      {
        ret_obj[j]=all_obj[i];
        j++;
      }
    }
  }
  return ret_obj;
}

</script>';

	}

}

?>