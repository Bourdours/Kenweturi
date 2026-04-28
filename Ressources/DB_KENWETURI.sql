CREATE TABLE city (
   id INT AUTO_INCREMENT,
   name VARCHAR(100) NOT NULL,
   zipcode VARCHAR(10) NOT NULL,
   PRIMARY KEY (id)
);

CREATE TABLE journey_status (
   id INT AUTO_INCREMENT,
   label VARCHAR(50) NOT NULL,
   PRIMARY KEY (id)
);

CREATE TABLE user_status (
   id INT AUTO_INCREMENT,
   label VARCHAR(50) NOT NULL,
   PRIMARY KEY (id)
);

CREATE TABLE users (
   id INT AUTO_INCREMENT,
   firstname VARCHAR(50) NOT NULL,
   lastname VARCHAR(50) NOT NULL,
   email VARCHAR(100) NOT NULL,
   password_hash VARCHAR(255) NOT NULL,
   registered_at DATETIME,
   avatar VARCHAR(255),
   birth_date DATE,
   is_admin BOOLEAN DEFAULT FALSE,
   user_status_id INT,
   city_id INT NOT NULL,
   PRIMARY KEY (id),
   UNIQUE (email),
   FOREIGN KEY (user_status_id) REFERENCES user_status(id),
   FOREIGN KEY (city_id) REFERENCES city(id)
);

CREATE TABLE cars (
   id INT AUTO_INCREMENT,
   brand VARCHAR(50) NOT NULL,
   model VARCHAR(50) NOT NULL,
   color VARCHAR(50) NOT NULL,
   seats INT NOT NULL,
   user_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE location (
   id INT AUTO_INCREMENT,
   name VARCHAR(100) NOT NULL,
   address VARCHAR(255) NOT NULL,
   longitude DECIMAL(9,6) NOT NULL,
   latitude DECIMAL(9,6) NOT NULL,
   city_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (city_id) REFERENCES city(id)
);

CREATE TABLE journey_request (
   id INT AUTO_INCREMENT,
   date DATETIME NOT NULL,
   flexibility_minutes INT,
   location_start_id INT NOT NULL,
   location_end_id INT NOT NULL,
   user_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (location_start_id) REFERENCES location(id),
   FOREIGN KEY (location_end_id) REFERENCES location(id),
   FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE journey (
   id INT AUTO_INCREMENT,
   seats INT NOT NULL,
   departure_time DATETIME NOT NULL,
   status_id INT NOT NULL,
   location_start_id INT NOT NULL,
   location_end_id INT NOT NULL,
   user_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (status_id) REFERENCES journey_status(id),
   FOREIGN KEY (location_start_id) REFERENCES location(id),
   FOREIGN KEY (location_end_id) REFERENCES location(id),
   FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE message (
   id INT AUTO_INCREMENT,
   content TEXT NOT NULL,
   sent_at DATETIME NOT NULL,
   is_read BOOLEAN NOT NULL DEFAULT FALSE,
   journey_id INT NOT NULL,
   user_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (journey_id) REFERENCES journey(id),
   FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE booking (
   user_id INT,
   journey_id INT,
   created_at DATETIME NOT NULL,
   PRIMARY KEY (user_id, journey_id),
   FOREIGN KEY (user_id) REFERENCES users(id),
   FOREIGN KEY (journey_id) REFERENCES journey(id)
);

CREATE TABLE reporting (
   id INT AUTO_INCREMENT,
   description VARCHAR(1000) NOT NULL,
   user_id INT NOT NULL,
   journey_id INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (user_id) REFERENCES users(id),
   FOREIGN KEY (journey_id) REFERENCES journey(id)
);