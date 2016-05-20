
-- Load crawled data into database
LOAD DATA LOCAL INFILE '/media/sf_sandbox/CS246/classes.csv' INTO TABLE Class
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '"'
(id, cid, quarter, year, dept, cnum, title, instructor, type, days, @vstart, @vstop, building, room, res, enrollment, enrollmentcap, waitlist, waitlistcap, status)
SET 
	start = NULLIF(@vstart,''),
	stop = NULLIF(@vstop,'')
;


-- LOAD DATA LOCAL INFILE '~/data/disc.csv' INTO TABLE Disc
-- FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';