-- =============================================
-- FitHub - Database Schema (SQLite)
-- =============================================

-- Tabela de utilizadores (login/registo)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'member',       
    profile_pic TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de treinadores
CREATE TABLE IF NOT EXISTS trainers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    specialties TEXT NOT NULL,     
    rating REAL DEFAULT 0.0,
    reviews_count INTEGER DEFAULT 0,
    bio TEXT,
    photo_initials TEXT NOT NULL   
);

-- Tabela de aulas
CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    day_of_week TEXT NOT NULL,     
    time_start TEXT NOT NULL,      
    time_end TEXT NOT NULL,        
    location TEXT NOT NULL,
    trainer_id INTEGER,
    total_spots INTEGER NOT NULL,
    booked_spots INTEGER DEFAULT 0,
    type TEXT NOT NULL,            
    rating REAL DEFAULT 0.0,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

-- Tabela de inscrições (bookings)
CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    UNIQUE(user_id, class_id)     
);

-- Tabela de equipamento
CREATE TABLE IF NOT EXISTS equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL,        
    quantity INTEGER DEFAULT 1,
    description TEXT,
    status TEXT NOT NULL DEFAULT 'available'
);

-- Tabela de reviews de treinadores
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    trainer_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK(rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

CREATE TABLE IF NOT EXISTS member_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    weight REAL NOT NULL,
    body_fat REAL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);