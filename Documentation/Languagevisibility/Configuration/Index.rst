

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


Configuration
-------------

Use this TYPOScript Code to set up your languagebehavior in the FE:

::

   config.sys_language_mode = ignore 
   config.sys_language_overlay = hideNonTranslated 
   
   //normal language configuration:
   config.sys_language_uid = 0 
   config.language = en 
   config.htmlTag_langKey = en 
   config.locale_all = en_GB.utf8 
   
   //deutsch
    [globalVar = GP:L=1] 
     config.sys_language_uid = 1
            config.language = de 
          config.htmlTag_langKey = de 
           config.locale_all = de_DE.utf8 
   [global]
   ...


