-- database/schema.sql

-- Tabel Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'member' CHECK (role IN ('admin', 'member')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Books (Buku)
CREATE TABLE books (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    publisher VARCHAR(150),
    published_year INTEGER,
    isbn VARCHAR(50) UNIQUE,
    stock INTEGER DEFAULT 1,
    cover_image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Loans (Peminjaman)
CREATE TABLE loans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    book_id INTEGER REFERENCES books(id) ON DELETE CASCADE,
    loan_date DATE,
    due_date DATE,
    return_date DATE,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'dipinjam', 'dikembalikan', 'ditolak')),
    fine INTEGER DEFAULT 0,
    fine_status VARCHAR(20) DEFAULT 'unpaid' CHECK (fine_status IN ('unpaid', 'paid')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
