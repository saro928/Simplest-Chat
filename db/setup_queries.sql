CREATE DATABASE chat;
USE chat;

-- Create tables
CREATE TABLE users(
	id INTEGER AUTO_INCREMENT,
    name VARCHAR(50),
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('offline', 'online') DEFAULT 'offline',
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE messages(
	message_id INTEGER AUTO_INCREMENT,
    message TEXT,
    user_id SMALLINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    room_id INTEGER DEFAULT 1,
    PRIMARY KEY (message_id),
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (room_id) REFERENCES chat_rooms (room_id)
) ENGINE = InnoDB;

-- The General Room where all new Users are headed is room_id = 1, so be sure to create this Room after the creation of tables and before running the Chat
CREATE TABLE chat_rooms(
	room_id INTEGER AUTO_INCREMENT,
    PRIMARY KEY (room_id)
) ENGINE = InnoDB;

CREATE TABLE chat_rooms_has_users (
    room_id INTEGER DEFAULT 1,
    user_id INTEGER,
    PRIMARY KEY (room_id, user_id),  
    FOREIGN KEY (room_id) REFERENCES chat_rooms (room_id),
    FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE = InnoDB;

-- As mentioned before, run this query to create the room_id = 1, it will be set to 1 because of A_I field
INSERT INTO chat_rooms (room_id) VALUES (0);

/* Recurring Event that checks for online users */
-- Be sure to set this ON every time the computer is rebooted
SET GLOBAL event_scheduler = ON;
-- Now you can see the event scheduler
SHOW PROCESSLIST;
-- The Event
DELIMITER $$
CREATE EVENT set_users_offline
    ON SCHEDULE EVERY 10 SECOND
    DO  
		BEGIN
			SET GLOBAL sql_safe_updates = 0;
			UPDATE users SET status = IF(TIMESTAMPDIFF(SECOND, last_seen, CURRENT_TIMESTAMP) > 5, 'offline', 'online');
			SET GLOBAL sql_safe_updates = 1;
        END$$
DELIMITER ;
-- When the Event is created you can check it out here
SHOW EVENTS;

-- Trigger for every new user insertion in users table, then insert that id in chat_rooms_has_users, the user will be assigned to room_id = 1 by default as seen in code of tables creation
DROP TRIGGER IF EXISTS after_user_insert;  
-- The Trigger
DELIMITER //
CREATE TRIGGER after_user_insert
AFTER INSERT
ON users
FOR EACH ROW
BEGIN
	INSERT INTO chat_rooms_has_users (user_id) VALUES (new.id);
END //
DELIMITER ;