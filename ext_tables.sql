#
# Table structure for table 'sys_language'
#
CREATE TABLE sys_language (
	tx_languagevisibility_fallbackorder blob NOT NULL,
	tx_languagevisibility_fallbackorderel blob NOT NULL,
	tx_languagevisibility_fallbackorderttnewsel blob NOT NULL,
	tx_languagevisibility_complexfallbacksetting smallint(6) DEFAULT '0' NOT NULL,
	tx_languagevisibility_defaultvisibility varchar(11) DEFAULT 't' NOT NULL,
	tx_languagevisibility_defaultvisibilityel varchar(11) DEFAULT 't' NOT NULL,
	tx_languagevisibility_defaultvisibilityttnewsel varchar(11) DEFAULT 't' NOT NULL
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_languagevisibility_visibility text NULL,
	tx_languagevisibility_inheritanceflag_original tinyint(1) DEFAULT '0' NOT NULL,
	tx_languagevisibility_inheritanceflag_overlayed tinyint(1) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
	tx_languagevisibility_visibility text NOT NULL,
	tx_languagevisibility_inheritanceflag_original tinyint(1) DEFAULT '0' NOT NULL,
	tx_languagevisibility_inheritanceflag_overlayed tinyint(1) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_languagevisibility_visibility text NOT NULL
);

#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
	tx_languagevisibility_visibility text NOT NULL
);

#
# Table structure for table 'be_groups'
#
CREATE TABLE be_groups (
	tx_languagevisibility_allow_movecutdelete_foroverlays tinyint(1) DEFAULT '0' NOT NULL
);
