

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


Extend your table
^^^^^^^^^^^^^^^^^

Add this definition to your table TCA configuration:

::

   "tx_languagevisibility_visibility" => Array (
               "exclude" => 1,
               "label" => "LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility",
               "config" => Array (
                   "type" => "user",
                   "size" => "30",
                   "userFunc" => 'tx_languagevisibility_fieldvisibility->user_fieldvisibility',
               )
           )

And to ext\_tables.sql add:

::

   tx_languagevisibility_visibility text NOT NULL

