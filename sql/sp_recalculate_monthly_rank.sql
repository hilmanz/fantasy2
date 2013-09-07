DELIMITER $$

USE `ffg`$$

DROP PROCEDURE IF EXISTS `recalculate_monthly_rank`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `recalculate_monthly_rank`()
BEGIN 
DECLARE isDone BOOLEAN DEFAULT FALSE;
DECLARE i INT DEFAULT 1;
DECLARE a BIGINT(11);
DECLARE b INT(3);
DECLARE c INT(4);
DECLARE d INT(11);
DECLARE curs CURSOR FOR 
	SELECT team_id,bln,thn,SUM(points) AS points
	FROM (SELECT team_id,YEAR(matchdate) AS thn,MONTH(matchdate) AS bln, points
	FROM ffg.weekly_points) g GROUP BY thn,bln,team_id ORDER BY points DESC;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET isDone = TRUE;
OPEN curs;
	SET isDone = FALSE;
	SET i = 1;
	REPEAT
		FETCH curs INTO a,b,c,d;
		IF a IS NOT NULL THEN
			INSERT INTO monthly_points
			(team_id,bln,thn,points,rank)
			VALUES
			(a,b,c,d,i)
			ON DUPLICATE KEY UPDATE
			rank = VALUES(rank);
	
		END IF;
		SET i = i + 1;
		SET a = NULL;
		SET b = NULL;
		SET c = NULL;
		SET d = NULL;
	UNTIL isDone END REPEAT;
CLOSE curs;
END$$

DELIMITER ;