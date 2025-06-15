-- Create the database
CREATE DATABASE IF NOT EXISTS art_gallery_db;
USE art_gallery_db;

-- Customer table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Artist table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nationality VARCHAR(100) NOT NULL,
    birthdate DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Room table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Staff table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role ENUM('Manager', 'Curator', 'Security', 'Guide', 'Administrator') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exhibition table
CREATE TABLE IF NOT EXISTS exhibitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    room_id INT,
    staff_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
);

-- Artwork table
CREATE TABLE IF NOT EXISTS artworks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    medium VARCHAR(255) NOT NULL,
    status ENUM('Available', 'On Display', 'Sold', 'On Loan', 'In Storage') DEFAULT 'Available',
    artist_id INT,
    room_id INT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE SET NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- Ticket table
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    exhibition_id INT,
    purchase_date DATE NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (exhibition_id) REFERENCES exhibitions(id) ON DELETE CASCADE
);

-- Purchase table
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    artwork_id INT,
    purchase_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO customers (name, email, phone, address) VALUES
('John Smith', 'john.smith@email.com', '+1-555-0101', '123 Main St, New York, NY 10001'),
('Emma Johnson', 'emma.johnson@email.com', '+1-555-0102', '456 Oak Ave, Los Angeles, CA 90210'),
('Michael Brown', 'michael.brown@email.com', '+1-555-0103', '789 Pine Rd, Chicago, IL 60601');

INSERT INTO artists (name, nationality, birthdate) VALUES
('Vincent van Gogh', 'Dutch', '1853-03-30'),
('Pablo Picasso', 'Spanish', '1881-10-25'),
('Leonardo da Vinci', 'Italian', '1452-04-15'),
('Claude Monet', 'French', '1840-11-14');

INSERT INTO rooms (name, capacity) VALUES
('Main Gallery', 100),
('East Wing', 75),
('West Wing', 75),
('Private Collection Room', 25),
('Sculpture Hall', 150);

INSERT INTO staff (name, role, email, phone) VALUES
('Sarah Wilson', 'Manager', 'sarah.wilson@gallery.com', '+1-555-0201'),
('David Chen', 'Curator', 'david.chen@gallery.com', '+1-555-0202'),
('Maria Garcia', 'Security', 'maria.garcia@gallery.com', '+1-555-0203'),
('James Taylor', 'Guide', 'james.taylor@gallery.com', '+1-555-0204');

INSERT INTO exhibitions (name, start_date, end_date, room_id, staff_id) VALUES
('Impressionist Masters', '2024-01-15', '2024-04-15', 1, 2),
('Modern Art Collection', '2024-02-01', '2024-05-01', 2, 2),
('Renaissance Revival', '2024-03-01', '2024-06-01', 3, 2);

INSERT INTO artworks (title, year, medium, status, artist_id, room_id, price) VALUES
('Starry Night', 1889, 'Oil on canvas', 'On Display', 1, 1, 100000000.00),
('Les Demoiselles d\'Avignon', 1907, 'Oil on canvas', 'On Display', 2, 2, 179000000.00),
('Mona Lisa', 1503, 'Oil on poplar', 'On Display', 3, 3, 850000000.00),
('Water Lilies', 1919, 'Oil on canvas', 'Available', 4, 1, 40000000.00);
