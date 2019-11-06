TRUNCATE emailing.baseok;
LOAD DATA LOCAL INFILE '/application/bdd/base_complete.csv' INTO TABLE baseok FIELDS TERMINATED BY ';' IGNORE 1 ROWS;