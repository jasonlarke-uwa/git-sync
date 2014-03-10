CREATE TABLE locale (
	id varchar(10) NOT NULL PRIMARY KEY,
	language varchar(50) NOT NULL
) Engine=InnoDB;

CREATE TABLE locale_namespace (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	namespace varchar(25) NOT NULL UNIQUE
) Engine=InnoDB;

CREATE TABLE locale_message (
	locale_id		varchar(10) NOT NULL,
	namespace_id 	INT UNSIGNED NOT NULL,
	name			varchar(25) NOT NULL,
	message			varchar(1000) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
	active			varchar(1) DEFAULT 'Y' NOT NULL,
	created_date	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	modified_date	TIMESTAMP,
	
	CONSTRAINT FOREIGN KEY(locale_id) REFERENCES locale(id) ON DELETE CASCADE,
	CONSTRAINT FOREIGN KEY(namespace_id) REFERENCES locale_namespace(id) ON DELETE CASCADE,
	PRIMARY KEY(locale_id,namespace_id,name)
) Engine=InnoDB;