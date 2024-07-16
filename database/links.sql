DROP TABLE IF EXISTS links;
CREATE TABLE links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL,
    title TEXT NOT NULL,
    domain TEXT NOT NULL,
    addeddate TEXT NOT NULL,
    expiry INT NOT NULL,
    snoozes INT,
    read INT
);
DROP TABLE IF EXISTS snoozeall;
CREATE TABLE snoozeall (
    endofsnooze INT
);
INSERT INTO snoozeall (endofsnooze) VALUES (0);
