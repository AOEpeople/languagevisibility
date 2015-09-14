

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Get your records the right way
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

extbase support:

For normal extbase collections it should work out of the box, since
extbase used TYPO3 Core overlay methods

For some versions you need a patch in extbase TYPO3 persitence class:

::

      protected function doLanguageAndWorkspaceOverlay(Tx_Extbase_Persistence_QOM_SourceInterface $source, array $rows, $languageUid = NULL, $workspaceUid = NULL) {
                   $overlayedRows = array();
                   foreach ($rows as $row) {
   +                       if (!isset($row['uid'])) {
   +                                $overlayedRows[] = $row;
   +                                continue;
   +                        }
   +
                           if (!($this->pageSelectObject instanceof t3lib_pageSelect)) {
                                   if (TYPO3_MODE == 'FE') {
                                           if (is_object($GLOBALS['TSFE'])) {
   @@ -986,7 +991,7 @@
                                           $row = $this->pageSelectObject->getRecordOverlay($tableName, $row, $languageUid, $overlayMode);
                                   }
                           }
   -                       if ($row !== NULL && is_array($row)) {
   +                       if ($row !== NULL && is_array($row) && $row['uid']>0) {
                                   $overlayedRows[] = $row;
                           }
                   }
   @@ -1064,4 +1069,4 @@
           }
    }
    

For other use-cases you can use the API to check your records:

::

   // get languagevisibility uid that is available (check for the correct uid to fall back to)
   $table = '<tablename>';
   $element = tx_languagevisibility_feservices::getElement($this->row['referenceid'], $table);
   $language_uid = tx_languagevisibility_feservices::getOverlayLanguageIdForElement($element, $GLOBALS['TSFE']->sys_language_uid);
    
   // get overlay record
   if($language_uid > 0) {
           $this->row = tx_mvc_system_dbtools::getTYPO3RowOverlay($this->row, $table, $language_uid);
   }

