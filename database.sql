-- Create database
CREATE DATABASE IF NOT EXISTS movie_database;
USE movie_database;

-- Drop tables if exist
DROP TABLE IF EXISTS movie_cast;
DROP TABLE IF EXISTS movie_genres;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS cast;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS users;

-- Users table with is_admin field
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Genres table
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cast table
CREATE TABLE cast (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movies table
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    rating DECIMAL(3,1) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movie-Genres junction table
CREATE TABLE movie_genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    genre_id INT NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    UNIQUE KEY (movie_id, genre_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movie-Cast junction table
CREATE TABLE movie_cast (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    cast_id INT NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (cast_id) REFERENCES cast(id) ON DELETE CASCADE,
    UNIQUE KEY (movie_id, cast_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin user (username: admin, password: admin123)
INSERT INTO users (username, password, is_admin) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert genres
INSERT INTO genres (name) VALUES 
('Action'), ('Adventure'), ('Animation'), ('Comedy'), ('Crime'), 
('Drama'), ('Fantasy'), ('Horror'), ('Mystery'), ('Romance'), 
('Sci-Fi'), ('Thriller'), ('Western'), ('Biography'), ('Documentary');

-- Insert cast members
INSERT INTO cast (name) VALUES 
('Leonardo DiCaprio'), ('Tom Hanks'), ('Morgan Freeman'), ('Brad Pitt'), 
('Robert Downey Jr.'), ('Scarlett Johansson'), ('Christian Bale'), 
('Natalie Portman'), ('Johnny Depp'), ('Matt Damon'), ('Jennifer Lawrence'), 
('Denzel Washington'), ('Will Smith'), ('Meryl Streep'), ('Tom Cruise'), 
('Keanu Reeves'), ('Harrison Ford'), ('Samuel L. Jackson'), 
('Angelina Jolie'), ('Chris Hemsworth');

-- Insert sample movies
INSERT INTO movies (title, year, rating, description) VALUES 
('The Shawshank Redemption', 1994, 9.3, 'Two imprisoned men bond over years, finding redemption through acts of common decency.'),
('The Godfather', 1972, 9.2, 'The aging patriarch of an organized crime dynasty transfers control to his reluctant son.'),
('The Dark Knight', 2008, 9.0, 'Batman must accept one of the greatest psychological and physical tests when the Joker wreaks havoc.'),
('Inception', 2010, 8.8, 'A thief who steals corporate secrets is given the task of planting an idea into someones mind.'),
('Forrest Gump', 1994, 8.8, 'The story unfolds from the perspective of an Alabama man with an IQ of 75.'),
('The Matrix', 1999, 8.7, 'A computer hacker learns about the true nature of his reality and his role in the war against its controllers.'),
('Interstellar', 2014, 8.6, 'A team of explorers travel through a wormhole in space to ensure humanity survival.'),
('The Avengers', 2012, 8.0, 'Earth mightiest heroes must learn to fight as a team to stop Loki and his alien army.'),
('Gladiator', 2000, 8.5, 'A former Roman General seeks vengeance against the corrupt emperor who murdered his family.'),
('The Departed', 2006, 8.5, 'An undercover cop and a mole attempt to identify each other while infiltrating an Irish gang.');

-- Insert movie-genre relationships
INSERT INTO movie_genres (movie_id, genre_id) VALUES 
(1, 6), (2, 5), (2, 6), (3, 1), (3, 5), (3, 6), 
(4, 1), (4, 11), (4, 12), (5, 6), (5, 10), 
(6, 1), (6, 11), (7, 2), (7, 6), (7, 11), 
(8, 1), (8, 2), (8, 11), (9, 1), (9, 2), (9, 6), 
(10, 5), (10, 6), (10, 12);

-- Insert movie-cast relationships
INSERT INTO movie_cast (movie_id, cast_id) VALUES 
(1, 3), (2, 4), (3, 7), (4, 1), (5, 2), 
(6, 16), (7, 10), (8, 5), (8, 6), (8, 20), 
(9, 12), (10, 1), (10, 10);
