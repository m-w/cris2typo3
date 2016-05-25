#
# Table structure for table 'be_users'
#

CREATE TABLE be_users (
	tx_cris_key int(11) unsigned NOT NULL,
	tx_srlanguagemenu_type int(11) unsigned DEFAULT '0' NOT NULL,
	tx_srlanguagemenu_languages tinyblob NOT NULL
);
