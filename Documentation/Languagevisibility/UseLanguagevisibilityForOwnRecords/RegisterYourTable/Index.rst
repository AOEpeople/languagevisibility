

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


Register your table
^^^^^^^^^^^^^^^^^^^

Use the existing registration Hook :

::

   $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['recordElementSupportedTables'][<table>]=array();

(this will handle your table like a default record element. If you
need more control you can also register your own visibility element
class)

