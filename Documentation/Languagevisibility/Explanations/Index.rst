

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


Explanations
------------

**The extension introduce a new concept „visibility“:**

That controls when a element is visible in a certain language ,
currently there are 4 visibility modi:

- show if translated

- show if translation in fallback-order exists

- show always (even if no translation -> then default language is forced
  to be shown, fallback is not considered)

- show never (even if a translation exist) this can be controled global
  (=per language) and local (=per element)

This can be set for every supported record in TYPO3. Therefore it is
possible to have elements that are only visible in some languages.

**new settings for languages:**

- fallback order can be defined in a user friendly way with a multi
  select. You are able to select x fallback-languages for each language

- default visibility for pages

- default visibility for news

- default visibility for elements

**new BE Modul to check visibility**

**Introduce new FCE mode with normal overlay records:**

<langDisable>1</langDisable><langDatabaseOverlay>1</langDatabaseOverla
y>enables independent translation in workspaces.

Support for TCA configuration „l10n\_mode“ in overlaying process


