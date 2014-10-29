EXT:languagevisibility
======================

[![Build Status](https://travis-ci.org/AOEpeople/languagevisibility.svg)](https://travis-ci.org/AOEpeople/languagevisibility)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AOEpeople/languagevisibility/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AOEpeople/languagevisibility/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/AOEpeople/languagevisibility/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/AOEpeople/languagevisibility/?branch=master)

Add new translationMode for FCEs:

```
<langDatabaseOverlay>1</langDatabaseOverlay>:

XML fields are merged with default record xml row if:
<TCEforms type="array">
	<l10n_mode>mergeIfNotBlank</l10n_mode>
<TCEforms type="array">
	<l10n_mode>exclude</l10n_mode>
(use this for container fields for example)
```


Performance:
* add index to sys_language_uid  and l18n_parent

Contributing
------------

e.g.

	1. Fork the repository on Github
	2. Create a named feature branch (like `add_component_x`)
	3. Write your change
	4. Write tests for your change (if applicable)
	5. Run the tests, ensuring they all pass
	6. Submit a Pull Request using Github

