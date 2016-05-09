-- VARCHAR size will change based observations from crawled dataset 
-- define database table schema
CREATE TABLE Class(
	id 				INT NOT NULL AUTO_INCREMENT,
	-- attributes that identifies a given lecture
	-- lcid/(dept+cnum) + quarter + year will be used to identify the course
	cid				INT, 					-- Course id for the lecture e.g. 187093200 for CS31 (same across all quarter/year)
	quarter			VARCHAR(10) NOT NULL,	
	year			VARCHAR(5) 	NOT NULL,
	-- attributes common between lecture and associated discussion -- 
	dept			VARCHAR(20),			-- e.g. "Computer Science", "Anthropology"
	cnum 			VARCHAR(20),			-- Course number e.g. 31A in "CS31A"
	title			VARCHAR(50),			-- Course title. some of the classes have more than 1 lectures and each with a different title. We should capture them as two different classes. "CUR TOP-DATA STRCTR: Cloud computing" and "CUR TOP-DATA STRCTR: Basic Data Science"
	-- attributes specific to lectures -- 
	instructor		VARCHAR(50),			-- Course instructor
	type			VARCHAR(3),				-- From the first level, usually in bold. LEC, TUT, etc.
	sec				VARCHAR(5),				-- Lecture section number
	days			VARCHAR(5),				-- Day of Class MW, F, 
	start			time,					-- Start time. Will need to perform arithmetics in a 
	stop			time,					-- End time
	building		VARCHAR(20),
	room			VARCHAR(20),
	res 			VARCHAR(5),
	enrollment 		INT,
	enrollmentcap	INT,
	waitlist 		INT,
	waitlistcap		INT,
	status			VARCHAR(20),
	PRIMARY KEY (id)
);

-- CREATE TABLE Disc(
-- 	id				INT,					-- Used to uniquely identify all records in Disc table
-- 	-- attributes that identifies the associated lecture
-- 	-- lcid/(dept+cnum) + quarter + year will be used to identify the lecture to which the discussion belongs
-- 	lcid			INT, 					-- Course id for the lecture e.g. 187093201 for CS31 DISC 1A 
-- 	quarter			INT,
-- 	year			INT NOT NULL,
-- 	-- attributes for a given discussion -- 
-- 	dcid			INT,					-- Course id for the discussion 
-- 	type			VARCHAR(3),				-- DIS, SEM, LAB etc.
-- 	sec				VARCHAR(5),				-- Lecture and Discussion section number
-- 	days			VARCHAR(5),				-- Day of Class MW, F, 
-- 	start			time,					-- Start time. Will need to perform arithmetics in a 
-- 	stop			time,					-- End time
-- 	building		VARCHAR(20),
-- 	room			VARCHAR(20),
-- 	res 			VARCHAR(5),
-- 	enrollment 		INT,
-- 	enrollmentcap	INT,
-- 	waitlist 		INT,
-- 	waitlistcap		INT,
-- 	status			VARCHAR(20),
-- 	PRIMARY KEY (id)
-- 	FOREIGN KEY (id) REFERENCES Class(id); 	-- Tentative, need to determine if cid is unique in Class
-- );
