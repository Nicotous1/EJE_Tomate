
ALTER TABLE `entreprise` CHANGE `type` `type` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;
ALTER TABLE `entreprise` CHANGE `secteur` `secteur` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;
UPDATE `entreprise` SET `type`=NULL,`secteur`=NULL;
ALTER TABLE `entreprise` CHANGE `type` `type` TINYINT(50) NOT NULL, CHANGE `secteur` `secteur` TINYINT(50) NOT NULL;