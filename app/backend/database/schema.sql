CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS citext;

CREATE TYPE user_role AS ENUM ('customer','seller','manager','super_admin');
CREATE TYPE order_status AS ENUM ('pending','confirmed','preparing','shipped','delivered','cancelled');
CREATE TYPE payment_status AS ENUM ('unpaid','pending','paid','failed','refunded');

CREATE TABLE customers (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(160) NOT NULL,
  phone VARCHAR(30) NOT NULL UNIQUE,
  email CITEXT UNIQUE,
  password_hash TEXT,
  customer_no VARCHAR(40) UNIQUE,
  national_id VARCHAR(20) UNIQUE,
  role user_role NOT NULL DEFAULT 'customer',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  phone_verified_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE addresses (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_id UUID NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
  title VARCHAR(80),
  recipient_name VARCHAR(160) NOT NULL,
  recipient_phone VARCHAR(30) NOT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  address TEXT NOT NULL,
  postal_code VARCHAR(20),
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE categories (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  description TEXT,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE products (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  category_id UUID REFERENCES categories(id) ON DELETE SET NULL,
  name VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  description TEXT,
  price BIGINT NOT NULL CHECK (price >= 0),
  compare_at_price BIGINT CHECK (compare_at_price IS NULL OR compare_at_price >= 0),
  sku VARCHAR(80) UNIQUE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE product_images (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  product_id UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  url TEXT NOT NULL,
  alt_text VARCHAR(240),
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE inventory (
  product_id UUID PRIMARY KEY REFERENCES products(id) ON DELETE CASCADE,
  quantity INT NOT NULL DEFAULT 0 CHECK (quantity >= 0),
  reserved_quantity INT NOT NULL DEFAULT 0 CHECK (reserved_quantity >= 0),
  low_stock_threshold INT NOT NULL DEFAULT 5,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE coupons (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  code CITEXT NOT NULL UNIQUE,
  type VARCHAR(20) NOT NULL CHECK (type IN ('fixed','percent')),
  value BIGINT NOT NULL CHECK (value > 0),
  max_uses INT,
  used_count INT NOT NULL DEFAULT 0,
  starts_at TIMESTAMPTZ,
  expires_at TIMESTAMPTZ,
  is_active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE orders (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_number VARCHAR(32) NOT NULL UNIQUE,
  customer_id UUID REFERENCES customers(id) ON DELETE SET NULL,
  customer_name_snapshot VARCHAR(160) NOT NULL,
  customer_phone_snapshot VARCHAR(30) NOT NULL,
  shipping_address_snapshot JSONB NOT NULL,
  subtotal BIGINT NOT NULL CHECK (subtotal >= 0),
  discount BIGINT NOT NULL DEFAULT 0 CHECK (discount >= 0),
  shipping_cost BIGINT NOT NULL DEFAULT 0 CHECK (shipping_cost >= 0),
  total BIGINT NOT NULL CHECK (total >= 0),
  payment_status payment_status NOT NULL DEFAULT 'unpaid',
  order_status order_status NOT NULL DEFAULT 'pending',
  shipping_method VARCHAR(100),
  tracking_code VARCHAR(120),
  notes TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE order_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id UUID NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_id UUID REFERENCES products(id) ON DELETE SET NULL,
  product_name_snapshot VARCHAR(220) NOT NULL,
  sku_snapshot VARCHAR(80),
  unit_price BIGINT NOT NULL CHECK (unit_price >= 0),
  quantity INT NOT NULL CHECK (quantity > 0),
  total_price BIGINT NOT NULL CHECK (total_price >= 0)
);

CREATE TABLE payments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id UUID NOT NULL REFERENCES orders(id) ON DELETE RESTRICT,
  gateway VARCHAR(60) NOT NULL,
  authority VARCHAR(180),
  reference_id VARCHAR(180),
  amount BIGINT NOT NULL CHECK (amount > 0),
  status payment_status NOT NULL DEFAULT 'pending',
  raw_callback JSONB,
  paid_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(gateway, authority)
);

CREATE TABLE carts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_id UUID REFERENCES customers(id) ON DELETE CASCADE,
  guest_token UUID UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  CHECK (customer_id IS NOT NULL OR guest_token IS NOT NULL)
);

CREATE TABLE cart_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  cart_id UUID NOT NULL REFERENCES carts(id) ON DELETE CASCADE,
  product_id UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  quantity INT NOT NULL CHECK (quantity > 0),
  UNIQUE(cart_id, product_id)
);

CREATE TABLE site_settings (
  key VARCHAR(100) PRIMARY KEY,
  value JSONB NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE audit_logs (
  id BIGSERIAL PRIMARY KEY,
  actor_id UUID REFERENCES customers(id) ON DELETE SET NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80),
  entity_id UUID,
  metadata JSONB,
  ip INET,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(order_status, payment_status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_audit_entity ON audit_logs(entity_type, entity_id);


-- Sanctum personal access tokens (API bearer auth)
CREATE TABLE IF NOT EXISTS personal_access_tokens (
  id BIGSERIAL PRIMARY KEY,
  tokenable_type VARCHAR(255) NOT NULL,
  tokenable_id UUID NOT NULL,
  name VARCHAR(255) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  abilities TEXT,
  last_used_at TIMESTAMPTZ NULL,
  expires_at TIMESTAMPTZ NULL,
  created_at TIMESTAMPTZ NULL,
  updated_at TIMESTAMPTZ NULL
);
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_index
ON personal_access_tokens(tokenable_type, tokenable_id);

-- Demo data for Render smoke testing
-- Default test administrator: admin / 44953322. Change it before production.
INSERT INTO customers (id,name,phone,password_hash,role,is_active,customer_no)
VALUES ('30000000-0000-0000-0000-000000000001','مدیر MWoodi','admin','$2y$12$uPy5m3JrLCYT2Rca/SQD4OIFmJx8X.pttngO0KpsAom.JC0.BzyPe','super_admin',true,'ADMIN-0001')
ON CONFLICT (phone) DO NOTHING;

INSERT INTO categories (id,name,slug,description,sort_order)
VALUES
('10000000-0000-0000-0000-000000000001','آشپزخانه','kitchen','محصولات چوبی آشپزخانه',1),
('10000000-0000-0000-0000-000000000002','دکوراسیون','decor','محصولات دکوراتیو',2),
('10000000-0000-0000-0000-000000000003','پذیرایی','reception','محصولات پذیرایی',3)
ON CONFLICT (id) DO NOTHING;

INSERT INTO products (id,category_id,name,slug,description,price,sku,is_active)
VALUES
('20000000-0000-0000-0000-000000000001','10000000-0000-0000-0000-000000000001','سینی چوبی دست‌ساز','wood-tray','سینی چوبی دست‌ساز MWoodi',890000,'MW-P1',true),
('20000000-0000-0000-0000-000000000002','10000000-0000-0000-0000-000000000002','استند چوبی مینیمال','minimal-stand','استند چوبی مینیمال',690000,'MW-P2',true),
('20000000-0000-0000-0000-000000000003','10000000-0000-0000-0000-000000000003','جعبه پذیرایی چوبی','serving-box','جعبه پذیرایی چوبی',1250000,'MW-P3',true),
('20000000-0000-0000-0000-000000000004','10000000-0000-0000-0000-000000000001','تخته سرو طبیعی','serving-board','تخته سرو طبیعی',980000,'MW-P4',true),
('20000000-0000-0000-0000-000000000005','10000000-0000-0000-0000-000000000002','جا شمعی چوبی','candle-holder','جا شمعی چوبی',490000,'MW-P5',true),
('20000000-0000-0000-0000-000000000006','10000000-0000-0000-0000-000000000002','باکس چوبی رومیزی','desk-box','باکس چوبی رومیزی',760000,'MW-P6',true)
ON CONFLICT (id) DO NOTHING;

INSERT INTO inventory(product_id,quantity,reserved_quantity)
SELECT id, 20, 0 FROM products
ON CONFLICT (product_id) DO NOTHING;
