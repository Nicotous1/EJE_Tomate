ALTER TABLE `etude`  ADD `but_short` VARCHAR(400) NULL  AFTER `pub_titre`,  ADD `per_rem` FLOAT NOT NULL  AFTER `but_short`;
ALTER TABLE `etude` CHANGE `per_rem` `per_rem` INT NOT NULL DEFAULT '60';