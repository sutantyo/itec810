CREATE TABLE `sequence` ( 
`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,  
`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,  
`permissions_group` VARCHAR(250) NOT NULL 
) ENGINE = InnoDB;

CREATE TABLE sequence_quiz` (
`sequence_id` INT(11) NOT NULL ,  
`quiz_id` INT(11) NOT NULL ,  
`position` INT(11) NOT NULL DEFAULT '1' ,    
PRIMARY KEY  (`sequence_id`, `quiz_id`) 
) ENGINE = InnoDB