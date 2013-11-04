DELIMITER $$

USE `ffg`$$

DROP PROCEDURE IF EXISTS `recalculate_rank`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `recalculate_rank`()
BEGIN 
DECLARE isDone BOOLEAN DEFAULT FALSE;
DECLARE i INT DEFAULT 1;
DECLARE a BIGINT(11);
DECLARE b INT(11);
DECLARE curs CURSOR FOR 
	SELECT a.team_id,(a.points + a.extra_points) AS points
	FROM points a
	INNER JOIN teams b
	ON a.team_id = b.id 
	ORDER BY (a.points+a.extra_points) DESC,a.team_id ASC;
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