-- Splash360 Tours - PostgreSQL Schema for Supabase
-- Run this in Supabase SQL Editor: https://supabase.com/dashboard/project/gscqdjoxnbywpinxfswh/sql/new

-- ============================================================
-- TENANTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS tenants (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','inactive','suspended')),
  api_key VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PLANS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS plans (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  price DECIMAL(10,2) DEFAULT 0.00,
  billing_cycle VARCHAR(20) DEFAULT 'monthly' CHECK (billing_cycle IN ('monthly','yearly','lifetime')),
  max_properties INT DEFAULT 5,
  max_tours INT DEFAULT 10,
  max_scenes_per_tour INT DEFAULT 20,
  max_storage_gb DECIMAL(10,2) DEFAULT 1.00,
  features TEXT DEFAULT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(30) DEFAULT 'user' CHECK (role IN ('platform_admin','tenant_admin','user')),
  status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','inactive')),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SUBSCRIPTIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS subscriptions (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  plan_id INT NOT NULL REFERENCES plans(id),
  status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','inactive','cancelled','expired')),
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- INVOICES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS invoices (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  subscription_id INT DEFAULT NULL REFERENCES subscriptions(id),
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','paid','failed','cancelled')),
  due_date DATE DEFAULT NULL,
  paid_at TIMESTAMP DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PAYMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  invoice_id INT DEFAULT NULL REFERENCES invoices(id),
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(50) DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','completed','failed','refunded')),
  transaction_id VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PROPERTIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS properties (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  name VARCHAR(255) NOT NULL,
  address TEXT DEFAULT NULL,
  city VARCHAR(255) DEFAULT NULL,
  country VARCHAR(255) DEFAULT NULL,
  type VARCHAR(50) DEFAULT 'residential' CHECK (type IN ('residential','commercial','industrial','land','other')),
  status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','inactive')),
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TOURS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS tours (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  property_id INT DEFAULT NULL REFERENCES properties(id) ON DELETE SET NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
  is_public BOOLEAN DEFAULT FALSE,
  thumbnail VARCHAR(500) DEFAULT NULL,
  settings TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SCENES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS scenes (
  id SERIAL PRIMARY KEY,
  tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
  tour_id INT NOT NULL REFERENCES tours(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  image_path VARCHAR(500) DEFAULT NULL,
  thumbnail_path VARCHAR(500) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_start_scene BOOLEAN DEFAULT FALSE,
  settings TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- HOTSPOTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS hotspots (
  id SERIAL PRIMARY KEY,
  scene_id INT NOT NULL REFERENCES scenes(id) ON DELETE CASCADE,
  type VARCHAR(30) DEFAULT 'navigation' CHECK (type IN ('navigation','info','link')),
  title VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  target_scene_id INT DEFAULT NULL REFERENCES scenes(id) ON DELETE SET NULL,
  url VARCHAR(500) DEFAULT NULL,
  pitch DECIMAL(10,6) DEFAULT 0,
  yaw DECIMAL(10,6) DEFAULT 0,
  icon VARCHAR(50) DEFAULT 'arrow',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TOUR VIEWS TABLE (Analytics)
-- ============================================================
CREATE TABLE IF NOT EXISTS tour_views (
  id SERIAL PRIMARY KEY,
  tour_id INT NOT NULL REFERENCES tours(id) ON DELETE CASCADE,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SESSIONS TABLE (database-backed sessions for Vercel)
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
  id VARCHAR(128) NOT NULL PRIMARY KEY,
  data TEXT NOT NULL,
  expires_at TIMESTAMP NOT NULL
);
CREATE INDEX IF NOT EXISTS sessions_expires_at ON sessions (expires_at);

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO plans (name, slug, price, billing_cycle, max_properties, max_tours, max_scenes_per_tour, max_storage_gb, is_active) VALUES
('Free', 'free', 0.00, 'monthly', 2, 3, 10, 0.50, TRUE),
('Starter', 'starter', 29.00, 'monthly', 10, 20, 30, 5.00, TRUE),
('Professional', 'professional', 79.00, 'monthly', 50, 100, 100, 20.00, TRUE),
('Enterprise', 'enterprise', 199.00, 'monthly', 999, 999, 999, 100.00, TRUE)
ON CONFLICT (slug) DO NOTHING;

-- Platform admin tenant
INSERT INTO tenants (name, email, status, api_key) VALUES
('Platform Admin', 'admin@splash360tours.com', 'active', 'sk_admin_key_here')
ON CONFLICT DO NOTHING;

-- Platform admin user (password: Admin@123456)
INSERT INTO users (tenant_id, name, email, password, role, status) VALUES
(1, 'Platform Admin', 'admin@splash360tours.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 'active')
ON CONFLICT (email) DO NOTHING;
