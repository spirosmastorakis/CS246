
-- Load crawled data into database
LOAD DATA LOCAL INFILE '/media/sf_sandbox/CS246/classes.csv' INTO TABLE Class
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

-- LOAD DATA LOCAL INFILE '~/data/disc.csv' INTO TABLE Disc
-- FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';