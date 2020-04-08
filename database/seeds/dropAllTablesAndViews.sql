DROP PROCEDURE IF EXISTS `drop_all_tables`;

DELIMITER $$
CREATE PROCEDURE `drop_all_tables`()
BEGIN
    DECLARE _done INT DEFAULT FALSE;
    DECLARE _tableName VARCHAR(255);
    DECLARE _cursor CURSOR FOR
        SELECT table_name
        FROM information_schema.TABLES
        WHERE table_schema = SCHEMA();
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _done = TRUE;

    SET FOREIGN_KEY_CHECKS = 0;

    OPEN _cursor;

    REPEAT FETCH _cursor INTO _tableName;

    IF NOT _done THEN
        SET @stmt_sql1 = CONCAT('DROP TABLE IF EXISTS ', _tableName);
        SET @stmt_sql2 = CONCAT('DROP VIEW IF EXISTS ', _tableName);

        PREPARE stmt1 FROM @stmt_sql1;
        PREPARE stmt2 FROM @stmt_sql2;

        EXECUTE stmt1;
        EXECUTE stmt2;

        DEALLOCATE PREPARE stmt1;
        DEALLOCATE PREPARE stmt2;
    END IF;

    UNTIL _done END REPEAT;

    CLOSE _cursor;
    SET FOREIGN_KEY_CHECKS = 1;
END$$

DELIMITER ;

call drop_all_tables();

DROP PROCEDURE IF EXISTS `drop_all_tables`;