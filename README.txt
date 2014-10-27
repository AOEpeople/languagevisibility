Build status:
https://travis-ci.org/tomasnorre/languagevisibility.svg?branch=TravisCi

Add new translationMode for FCEs:
	<langDatabaseOverlay>1</langDatabaseOverlay>:

	XML fields are merged with default record xml row if:
	<TCEforms type="array">
			<l10n_mode>mergeIfNotBlank</l10n_mode>
	<TCEforms type="array">
			<l10n_mode>exclude</l10n_mode>
	(use this for container fields for example)


Performance:
* add index to sys_language_uid  and l18n_parent
