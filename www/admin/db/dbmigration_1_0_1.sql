BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "visits" (
    "id"	INTEGER UNIQUE,
    "countryCode"	VARCHAR(2) NOT NULL,
    "regionName"	VARCHAR(64),
    "cityName"	TEXT,
    "lat"	REAL NOT NULL,
    "long"	REAL NOT NULL,
    "visit_count"	INTEGER NOT NULL,
    PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "dvmAdminlog" (
    "timestamp"	VARCHAR(50),
    "logger"	VARCHAR(256),
    "level"	VARCHAR(32),
    "message"	VARCHAR(4000),
    "file"	VARCHAR(255),
    "line"	VARCHAR(10)
);
CREATE TABLE IF NOT EXISTS "dvmDBver" (
    "id"	INTEGER,
    "dbVer"	TEXT,
    PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO "dvmDBver" ("dbVer") VALUES ('1.0.1');
CREATE INDEX IF NOT EXISTS "idx_latitude" ON "visits" (
    "lat"
);
CREATE INDEX IF NOT EXISTS "idx_longitude" ON "visits" (
    "long"
);
CREATE INDEX IF NOT EXISTS "idx_regions" ON "visits" (
    "regionName"
);
CREATE INDEX IF NOT EXISTS "idx_countries" ON "visits" (
    "countryCode"
);
CREATE INDEX IF NOT EXISTS "idx_dvmAdminlog" ON "dvmAdminlog" (
    "level",
    "file",
    "timestamp"
);
COMMIT;