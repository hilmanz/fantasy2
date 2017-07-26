DELIMITER $$

USE `fantasy`$$

DROP PROCEDURE IF EXISTS `recalculate_rank`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `recalculate_rank`()
BEGIN 
DECLARE isDone BOOLEAN DEFAULT FALSE;
DECLARE i INT DEFAULT 1;
DECLARE a BIGINT(11);
DECLARE b INT(11);
DECLARE curs CURSOR FOR 
	SELECT c.team_id,(c.points + c.extra_points) AS points FROM (
		SELECT team_id,points,extra_points FROM fantasy.points a
		WHERE EXISTS (SELECT 1 FROM fantasy.weekly_points b 
				  WHERE a.team_id = b.team_id LIMIT 1)
		UNION ALL
		SELECT team_id,-9999999 AS points,extra_points FROM fantasy.points a
		WHERE NOT EXISTS (SELECT 1 FROM fantasy.weekly_points b 
				  WHERE a.team_id = b.team_id LIMIT 1)) c
	ORDER BY (c.points + c.extra_points) DESC,c.team_id ASC;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET isDone = TRUE;
OPEN curs;
	SET isDone = FALSE;
	SET i = 1;
	REPEAT
		FETCH curs INTO a,b;
		IF a IS NOT NULL THEN
			UPDATE points SET rank = i WHERE team_id=a;
		END IF;
		SET i = i + 1;
		SET a = NULL;
		SET b = NULL;
	UNTIL isDone END REPEAT;
CLOSE curs;
END$$

DELIMITER ;