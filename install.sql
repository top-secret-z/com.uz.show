-- add column in user table
ALTER TABLE wcf1_user ADD showEntrys INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD INDEX showEntrys (showEntrys);

DROP TABLE IF EXISTS show1_entry;
CREATE TABLE show1_entry (
    entryID                    INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    isDisabled                TINYINT(1) NOT NULL DEFAULT 0,
    isDeleted                TINYINT(1) NOT NULL DEFAULT 0,
    deleteTime                INT(10) NOT NULL DEFAULT 0,
    time                    INT(10) NOT NULL DEFAULT 0,
    lastChangeTime            INT(10) NOT NULL DEFAULT 0,
    lastVersionTime            INT(10) NOT NULL DEFAULT 0,

    userID                    INT(10),
    username                VARCHAR(255) NOT NULL DEFAULT '',
    ipAddress                VARCHAR(39) NOT NULL DEFAULT '',

    enableComments            TINYINT(1) NOT NULL DEFAULT 1,
    enableHtml                TINYINT(1) NOT NULL DEFAULT 0,
    hasLabels                TINYINT(1) NOT NULL DEFAULT 0,
    isFeatured                TINYINT(1) NOT NULL DEFAULT 0,

    message                    MEDIUMTEXT,
    text2                    MEDIUMTEXT,
    text3                    MEDIUMTEXT,
    text4                    MEDIUMTEXT,
    text5                    MEDIUMTEXT,
    subject                    VARCHAR(255) NOT NULL DEFAULT '',
    teaser                    TEXT,
    hasEmbeddedObjects        TINYINT(1) NOT NULL DEFAULT 0,
    hasEmbeddedObjects2        TINYINT(1) NOT NULL DEFAULT 0,
    hasEmbeddedObjects3        TINYINT(1) NOT NULL DEFAULT 0,
    hasEmbeddedObjects4        TINYINT(1) NOT NULL DEFAULT 0,
    hasEmbeddedObjects5        TINYINT(1) NOT NULL DEFAULT 0,

    categoryID                INT(10),
    languageID                INT(10),

    attachmentID            INT(10),
    attachments                SMALLINT(5) NOT NULL DEFAULT 0,
    comments                SMALLINT(5) NOT NULL DEFAULT 0,
    cumulativeLikes            MEDIUMINT(7) NOT NULL DEFAULT 0,
    views                    INT(10) NOT NULL DEFAULT 0,

    iconExtension            VARCHAR(255) NOT NULL DEFAULT '',
    iconHash                VARCHAR(40) NOT NULL DEFAULT '',

    location                VARCHAR(255) NOT NULL DEFAULT '',
    latitude                FLOAT(10,7) NOT NULL DEFAULT 0.0,
    longitude                FLOAT(10,7) NOT NULL DEFAULT 0.0,

    KEY (attachments),
    KEY (comments),
    KEY (time),
    KEY (views)
);

DROP TABLE IF EXISTS show1_entry_option;
CREATE TABLE show1_entry_option (
    optionID                    INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    optionTitle                    VARCHAR(255) NOT NULL DEFAULT '',
    optionDescription            TEXT,
    optionType                    VARCHAR(255) NOT NULL DEFAULT '',
    defaultValue                MEDIUMTEXT,
    validationPattern            TEXT,
    selectOptions                MEDIUMTEXT,
    required                    TINYINT(1) NOT NULL DEFAULT 0,
    showOrder                    INT(10) NOT NULL DEFAULT 0,
    isDisabled                    TINYINT(1) NOT NULL DEFAULT 0,
    additionalData                MEDIUMTEXT,

    tab                            TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS show1_entry_option_value;
CREATE TABLE show1_entry_option_value (
    entryID                        INT(10) NOT NULL,
    optionID                    INT(10) NOT NULL,
    optionValue                    MEDIUMTEXT NOT NULL,

    UNIQUE KEY groupID (entryID, optionID)
);

DROP TABLE IF EXISTS show1_entry_to_category;
CREATE TABLE show1_entry_to_category (
    categoryID                    INT(10) NOT NULL,
    entryID                        INT(10) NOT NULL,
    PRIMARY KEY (categoryID, entryID)
);

DROP TABLE IF EXISTS show1_contact;
CREATE TABLE show1_contact (
    contactID                    INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userID                        INT(10),
    isDisabled                    TINYINT(1) NOT NULL DEFAULT 1,
    name                        VARCHAR(255) NOT NULL DEFAULT '',
    address                        TEXT NOT NULL,
    email                        VARCHAR(255) NOT NULL DEFAULT '',
    website                        VARCHAR(255) NOT NULL DEFAULT '',
    other                        TEXT NOT NULL,

    UNIQUE KEY userID (userID),
    KEY isDisabled (isDisabled)
);

ALTER TABLE show1_entry ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE show1_entry ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;
ALTER TABLE show1_entry ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE show1_entry ADD FOREIGN KEY (attachmentID) REFERENCES wcf1_attachment (attachmentID) ON DELETE SET NULL;

ALTER TABLE show1_entry_option_value ADD FOREIGN KEY (entryID) REFERENCES show1_entry (entryID) ON DELETE CASCADE;
ALTER TABLE show1_entry_option_value ADD FOREIGN KEY (optionID) REFERENCES show1_entry_option (optionID) ON DELETE CASCADE;

ALTER TABLE show1_entry_to_category ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE CASCADE;
ALTER TABLE show1_entry_to_category ADD FOREIGN KEY (entryID) REFERENCES show1_entry (entryID) ON DELETE CASCADE;

ALTER TABLE show1_contact ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
