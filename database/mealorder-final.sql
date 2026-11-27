-- MealOrder Final schema snapshot. IMPORT ONLY into a new empty database.
-- Alternative to php artisan migrate. Seeder must be run afterwards.
SET NAMES utf8mb4;
CREATE TABLE migrations (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,migration VARCHAR(255) NOT NULL,batch INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `users` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,email_verified_at TIMESTAMP NULL,password VARCHAR(255) NOT NULL,role ENUM('customer','admin') NOT NULL DEFAULT 'customer',phone VARCHAR(20) NULL,remember_token VARCHAR(100) NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `password_reset_tokens` (
 email VARCHAR(255) PRIMARY KEY,token VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `sessions` (
 id VARCHAR(255) PRIMARY KEY,user_id BIGINT UNSIGNED NULL,ip_address VARCHAR(45) NULL,user_agent TEXT NULL,payload LONGTEXT NOT NULL,last_activity INT NOT NULL,INDEX(user_id),INDEX(last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `cache` (
 `key` VARCHAR(255) PRIMARY KEY,value MEDIUMTEXT NOT NULL,expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `cache_locks` (
 `key` VARCHAR(255) PRIMARY KEY,owner VARCHAR(255) NOT NULL,expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `jobs` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,queue VARCHAR(255) NOT NULL,payload LONGTEXT NOT NULL,attempts TINYINT UNSIGNED NOT NULL,reserved_at INT UNSIGNED NULL,available_at INT UNSIGNED NOT NULL,created_at INT UNSIGNED NOT NULL,INDEX(queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `job_batches` (
 id VARCHAR(255) PRIMARY KEY,name VARCHAR(255) NOT NULL,total_jobs INT NOT NULL,pending_jobs INT NOT NULL,failed_jobs INT NOT NULL,failed_job_ids LONGTEXT NOT NULL,options MEDIUMTEXT NULL,cancelled_at INT NULL,created_at INT NOT NULL,finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `failed_jobs` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,uuid VARCHAR(255) NOT NULL UNIQUE,connection TEXT NOT NULL,queue TEXT NOT NULL,payload LONGTEXT NOT NULL,exception LONGTEXT NOT NULL,failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `personal_access_tokens` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,tokenable_type VARCHAR(255) NOT NULL,tokenable_id BIGINT UNSIGNED NOT NULL,name TEXT NOT NULL,token VARCHAR(64) NOT NULL UNIQUE,abilities TEXT NULL,last_used_at TIMESTAMP NULL,expires_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,INDEX(tokenable_type,tokenable_id),INDEX(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `categories` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE,description TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `products` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,category_id BIGINT UNSIGNED NOT NULL,name VARCHAR(150) NOT NULL,description TEXT NULL,price DECIMAL(12,2) UNSIGNED NOT NULL,stock INT UNSIGNED NOT NULL,image VARCHAR(255) NULL,is_available BOOLEAN NOT NULL DEFAULT TRUE,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `preorder_schedules` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,start_at DATETIME NOT NULL,end_at DATETIME NOT NULL,quota INT UNSIGNED NOT NULL,status ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',fulfillment_at DATETIME NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `orders` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,schedule_id BIGINT UNSIGNED NOT NULL,order_code VARCHAR(30) NOT NULL UNIQUE,total_amount DECIMAL(12,2) UNSIGNED NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'pending',notes TEXT NULL,fulfillment VARCHAR(12) NOT NULL DEFAULT 'pickup',delivery_address TEXT NULL,contact_phone VARCHAR(20) NULL,delivery_fee DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0,cancellation_reason TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,INDEX orders_status_created_at_index(status,created_at),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,FOREIGN KEY(schedule_id) REFERENCES preorder_schedules(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `order_items` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,product_name VARCHAR(150) NOT NULL,price DECIMAL(12,2) UNSIGNED NOT NULL,quantity INT UNSIGNED NOT NULL,subtotal DECIMAL(12,2) UNSIGNED NOT NULL,FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE RESTRICT,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `payments` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL UNIQUE,method VARCHAR(50) NOT NULL,amount DECIMAL(12,2) UNSIGNED NOT NULL,status ENUM('unpaid','waiting_confirmation','paid','rejected') NOT NULL DEFAULT 'unpaid',paid_at DATETIME NULL,FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `order_status_histories` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL,actor_id BIGINT UNSIGNED NULL,status VARCHAR(30) NOT NULL,reason TEXT NULL,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE RESTRICT,FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO migrations(migration,batch) VALUES ('0001_01_01_000000_create_users_table',1);
INSERT INTO migrations(migration,batch) VALUES ('0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations(migration,batch) VALUES ('0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000000_create_personal_access_tokens_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000001_create_categories_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000002_create_products_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000003_create_preorder_schedules_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000004_create_orders_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000005_create_order_items_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_01_01_000006_create_payments_table',1);
INSERT INTO migrations(migration,batch) VALUES ('2026_11_27_000001_add_fulfillment_and_history',1);

ALTER TABLE products ADD deleted_at TIMESTAMP NULL;
ALTER TABLE order_items ADD product_image VARCHAR(255) NULL;
INSERT INTO migrations(migration,batch) VALUES ('2026_11_27_000002_finalize_product_images',1);
