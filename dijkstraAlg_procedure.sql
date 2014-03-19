DROP PROCEDURE IF EXISTS dijkstraAlg;

DELIMITER //
CREATE PROCEDURE `dijkstraAlg`(
	in startpoint varchar(512),
	in endpoint varchar(512),
	in pids varchar(512),
	out resultset varchar(1024),
	out totalDist decimal(20,10)
)

BEGIN

	DECLARE done tinyint DEFAULT 0;
	DECLARE pt_from1, pt_to1, pt_from2, pt_to2, numberOfPoints int;
	DECLARE tempPoint1, tempPoint2 int;
	DECLARE pt_dist1, pt_dist2 decimal(20,10);
	SET numberOfPoints = (LENGTH(pids) - LENGTH(REPLACE(pids, ',', ''))) + 1;
	CREATE TABLE resultTable(
		fromID int,
		toID int,
		distance decimal(20,10)
	);
	INSERT INTO resultTable (fromID, toID, distance)
	select F.id as `from`, T.id as `to`, dist(F.lat, F.lng, T.lat, T.lng) 
	as dist
	from   marker F, marker T
	where  F.id < T.id and 
		find_in_set(convert(F.id, char(10)), pids) and
		find_in_set(convert(T.id, char(10)), pids)
    order by dist;
	
	IF numberOfPoints = 2 THEN
		SELECT fromID, toID, distance INTO pt_from1, pt_to1, pt_dist1
		FROM resultTable;
		SET resultset = CONCAT(pt_from1, ':', pt_to1);
		SET totalDist = pt_dist1;
	ELSE
		SELECT fromID, toID, distance INTO pt_from1, pt_to1, pt_dist1
		FROM resultTable
		WHERE (startpoint = fromID AND endpoint != toID)
			OR (endpoint != fromID AND startpoint = toID)
		order by distance LIMIT 1;
		IF pt_to1 = startpoint THEN
			SET pt_to1 = pt_from1;
			SET pt_from1 = startpoint;
		END IF;
		SET tempPoint1 = pt_to1;
		SET resultset = CONCAT(pt_from1, ':', pt_to1);
		SET totalDist = pt_dist1;
		SET numberOfPoints = numberOfPoints - 1;
		DELETE FROM resultTable
		WHERE (pt_from1 = fromID OR pt_from1 = toID);
		WHILE done != 1 DO
			IF numberOfPoints > 2 THEN
				SELECT fromID, toID, distance INTO pt_from2, pt_to2, pt_dist2
				FROM resultTable
				WHERE (tempPoint1 = fromID AND endPoint != toID AND startpoint != toID)
					OR (endpoint != fromID AND startpoint != fromID AND tempPoint1 = toID)
				order by distance LIMIT 1;
				IF pt_to2 = pt_to1 THEN
					SET pt_to2 = pt_from2;
					SET pt_from2 = pt_to1;
				END IF;
				SET resultset = CONCAT(resultset, ',', pt_from2, ':', pt_to2);
				SET totalDist = totalDist + pt_dist2;
				SET pt_from1 = pt_from2;
				SET pt_to1 = pt_to2;
				SET tempPoint1 = pt_to1;
				SET numberOfPoints = numberOfPoints - 1;
				DELETE FROM resultTable
				WHERE (pt_from2 = fromID OR pt_from2 = toID);
			ELSE
				SELECT fromID, toID, distance INTO pt_from2, pt_to2, pt_dist2
				FROM resultTable
				WHERE (tempPoint1 = fromID OR  tempPoint1 = toID)
					and (endpoint = fromID OR endpoint = toID)
				order by distance LIMIT 1;
				SET resultset = CONCAT(resultset, ',', pt_from2, ':', pt_to2);
				SET totalDist = totalDist + pt_dist2;
				SET done = 1;
			END IF;
		END WHILE;
	END IF;
	DROP TABLE resultTable;
	SELECT "Results" as "", resultset, totalDist;
END//
DELIMITER ;