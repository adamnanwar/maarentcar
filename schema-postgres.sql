-- ============================================================
-- We Rent Car — Database Schema (PostgreSQL 15+)
-- ============================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto"; -- untuk gen_random_uuid() bila dibutuhkan

-- ============================================================
-- 1. RBAC: roles, permissions
-- ============================================================

CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,          -- admin, staff, customer
    label VARCHAR(100) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    module VARCHAR(50) NOT NULL,               -- vehicles, bookings, payments, dst.
    action VARCHAR(50) NOT NULL,                -- view, create, update, delete, verify, ...
    slug VARCHAR(120) NOT NULL UNIQUE,          -- vehicles.create
    label VARCHAR(150) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE role_permissions (
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);

-- ============================================================
-- 2. Users & Addresses
-- ============================================================

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    role_id BIGINT NOT NULL REFERENCES roles(id),
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    avatar_path VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- active, inactive
    email_verified_at TIMESTAMPTZ,
    remember_token VARCHAR(100),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at TIMESTAMPTZ
);

CREATE INDEX idx_users_role ON users(role_id);

CREATE TABLE addresses (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    label VARCHAR(50),                   -- "Rumah", "Kantor", dst.
    recipient_name VARCHAR(150),
    phone VARCHAR(30),
    full_address TEXT NOT NULL,
    district VARCHAR(100),               -- kecamatan
    subdistrict VARCHAR(100),            -- kelurahan
    landmark VARCHAR(255),               -- patokan
    latitude NUMERIC(10,7),
    longitude NUMERIC(10,7),
    is_default BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_addresses_user ON addresses(user_id);

-- ============================================================
-- 3. Vehicles
-- ============================================================

CREATE TABLE vehicle_categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    icon_path VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE vehicles (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES vehicle_categories(id),
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    brand VARCHAR(100),
    model VARCHAR(100),
    year SMALLINT,
    plate_number VARCHAR(20) NOT NULL UNIQUE,  -- internal, tidak tampil publik
    transmission VARCHAR(20) NOT NULL DEFAULT 'manual', -- manual, automatic
    fuel_type VARCHAR(20) NOT NULL DEFAULT 'bensin',    -- bensin, diesel, listrik
    seat_capacity SMALLINT NOT NULL,
    price_per_day NUMERIC(12,2) NOT NULL,
    driver_fee_per_day NUMERIC(12,2) NOT NULL DEFAULT 0,
    base_delivery_fee NUMERIC(12,2) NOT NULL DEFAULT 0,
    description TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'tersedia', -- tersedia, perawatan, nonaktif
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at TIMESTAMPTZ
);

CREATE INDEX idx_vehicles_category_status ON vehicles(category_id, status);

CREATE TABLE vehicle_images (
    id BIGSERIAL PRIMARY KEY,
    vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN NOT NULL DEFAULT false,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_vehicle_images_vehicle ON vehicle_images(vehicle_id);

-- ============================================================
-- 4. Destinations & Tour Packages
-- ============================================================

CREATE TABLE destinations (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    category VARCHAR(50),               -- pantai, kuliner, sejarah, dll.
    description TEXT,
    address TEXT,
    image_path VARCHAR(255),
    addon_price NUMERIC(12,2) NOT NULL DEFAULT 0, -- biaya tambahan sebagai add-on
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE tour_packages (
    id BIGSERIAL PRIMARY KEY,
    vehicle_id BIGINT REFERENCES vehicles(id),   -- mobil representatif paket
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    seat_capacity SMALLINT NOT NULL,
    duration_days SMALLINT NOT NULL DEFAULT 1,
    driver_included BOOLEAN NOT NULL DEFAULT true,
    price NUMERIC(12,2) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE package_destinations (
    package_id BIGINT NOT NULL REFERENCES tour_packages(id) ON DELETE CASCADE,
    destination_id BIGINT NOT NULL REFERENCES destinations(id) ON DELETE CASCADE,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (package_id, destination_id)
);

-- ============================================================
-- 5. Bookings
-- ============================================================

CREATE TABLE bookings (
    id BIGSERIAL PRIMARY KEY,
    booking_code VARCHAR(30) NOT NULL UNIQUE,
    user_id BIGINT NOT NULL REFERENCES users(id),

    booking_type VARCHAR(20) NOT NULL,          -- mobil, paket_wisata
    vehicle_id BIGINT REFERENCES vehicles(id),
    package_id BIGINT REFERENCES tour_packages(id),

    start_datetime TIMESTAMPTZ NOT NULL,
    end_datetime TIMESTAMPTZ NOT NULL,
    duration_days SMALLINT NOT NULL,

    with_driver BOOLEAN NOT NULL DEFAULT false,
    delivery_method VARCHAR(30) NOT NULL,       -- pickup_at_office, delivered_to_address, driver_pickup

    pickup_address_snapshot JSONB,              -- salinan alamat saat booking dibuat
    passenger_count SMALLINT,

    base_price NUMERIC(12,2) NOT NULL DEFAULT 0,
    driver_fee NUMERIC(12,2) NOT NULL DEFAULT 0,
    delivery_fee NUMERIC(12,2) NOT NULL DEFAULT 0,
    addon_total NUMERIC(12,2) NOT NULL DEFAULT 0,
    discount NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_price NUMERIC(12,2) NOT NULL DEFAULT 0,

    status VARCHAR(30) NOT NULL DEFAULT 'menunggu_pembayaran',
    -- menunggu_pembayaran, menunggu_verifikasi, dikonfirmasi, berlangsung, selesai, ditolak, dibatalkan

    notes TEXT,
    internal_notes TEXT,

    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),

    CONSTRAINT chk_booking_type CHECK (
        (booking_type = 'mobil' AND vehicle_id IS NOT NULL AND package_id IS NULL)
        OR
        (booking_type = 'paket_wisata' AND package_id IS NOT NULL)
    ),
    CONSTRAINT chk_dates CHECK (end_datetime > start_datetime)
);

CREATE INDEX idx_bookings_vehicle_dates ON bookings(vehicle_id, start_datetime, end_datetime);
CREATE INDEX idx_bookings_user_status ON bookings(user_id, status);
CREATE INDEX idx_bookings_status_created ON bookings(status, created_at);

CREATE TABLE booking_destinations (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    destination_id BIGINT NOT NULL REFERENCES destinations(id),
    price_at_booking NUMERIC(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_booking_destinations_booking ON booking_destinations(booking_id);

CREATE TABLE booking_status_logs (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    from_status VARCHAR(30),
    to_status VARCHAR(30) NOT NULL,
    changed_by BIGINT REFERENCES users(id),
    note TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_booking_status_logs_booking ON booking_status_logs(booking_id);

-- ============================================================
-- 6. Payments
-- ============================================================

CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    amount NUMERIC(12,2) NOT NULL,
    bank_sender_name VARCHAR(150),
    bank_sender_account VARCHAR(50),
    proof_path VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'menunggu', -- menunggu, terverifikasi, ditolak
    paid_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_payments_booking_status ON payments(booking_id, status);

CREATE TABLE payment_verifications (
    id BIGSERIAL PRIMARY KEY,
    payment_id BIGINT NOT NULL REFERENCES payments(id) ON DELETE CASCADE,
    verified_by BIGINT NOT NULL REFERENCES users(id),
    action VARCHAR(20) NOT NULL,        -- verify, reject
    reason TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ============================================================
-- 7. Reviews
-- ============================================================

CREATE TABLE reviews (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL REFERENCES bookings(id),
    user_id BIGINT NOT NULL REFERENCES users(id),
    vehicle_id BIGINT REFERENCES vehicles(id),
    package_id BIGINT REFERENCES tour_packages(id),
    rating SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_hidden BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT chk_review_target CHECK (vehicle_id IS NOT NULL OR package_id IS NOT NULL)
);

CREATE INDEX idx_reviews_vehicle ON reviews(vehicle_id);
CREATE INDEX idx_reviews_package ON reviews(package_id);
CREATE UNIQUE INDEX uniq_review_per_booking ON reviews(booking_id);

-- ============================================================
-- 8. Settings
-- ============================================================

CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    type VARCHAR(20) NOT NULL DEFAULT 'text', -- text, json, image
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ============================================================
-- 9. Seed dasar (roles + contoh permission)
-- ============================================================

INSERT INTO roles (name, label) VALUES
  ('admin', 'Administrator'),
  ('staff', 'Staff Operasional'),
  ('customer', 'Pelanggan');

INSERT INTO permissions (module, action, slug, label) VALUES
  ('vehicles', 'view', 'vehicles.view', 'Lihat data mobil'),
  ('vehicles', 'create', 'vehicles.create', 'Tambah data mobil'),
  ('vehicles', 'update', 'vehicles.update', 'Ubah data mobil'),
  ('vehicles', 'delete', 'vehicles.delete', 'Hapus data mobil'),
  ('bookings', 'view', 'bookings.view', 'Lihat booking'),
  ('bookings', 'update_status', 'bookings.update_status', 'Ubah status booking'),
  ('payments', 'verify', 'payments.verify', 'Verifikasi pembayaran'),
  ('users', 'manage', 'users.manage', 'Kelola akun customer'),
  ('staff', 'manage', 'staff.manage', 'Kelola akun staff'),
  ('reports', 'view_full', 'reports.view_full', 'Lihat laporan penuh'),
  ('settings', 'manage', 'settings.manage', 'Kelola pengaturan sistem');

-- Admin dapat semua permission
INSERT INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name = 'admin'), id FROM permissions;

-- Staff hanya subset operasional
INSERT INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name = 'staff'), id
FROM permissions
WHERE slug IN ('vehicles.view', 'vehicles.update', 'bookings.view', 'bookings.update_status', 'payments.verify');
