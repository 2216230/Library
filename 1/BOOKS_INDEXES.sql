-- =====================================================
-- PHYSICAL BOOKS COLLECTION OPTIMIZATION - DATABASE INDEXES
-- =====================================================
-- Run these SQL commands to optimize books table
-- for 10,000+ records
--
-- Time to run: 2-5 minutes (depending on current data)
-- Performance improvement: 10-100x faster queries
--
-- Execute in phpMyAdmin or MySQL CLI:
-- mysql -u root -p libsystem5 < BOOKS_INDEXES.sql
-- =====================================================

USE libsystem5;

-- ✅ Add indexes for search fields
ALTER TABLE books ADD INDEX idx_title (title(100)) COMMENT 'For fast title search';
ALTER TABLE books ADD INDEX idx_author (author(100)) COMMENT 'For fast author search';
ALTER TABLE books ADD INDEX idx_isbn (isbn(20)) COMMENT 'For fast ISBN search';
ALTER TABLE books ADD INDEX idx_date_added (date_added DESC) COMMENT 'For sorting by date';

-- ✅ Add foreign key indexes
ALTER TABLE books ADD INDEX idx_section_id (section_id) COMMENT 'For section filtering';

-- ✅ Composite index for common searches
ALTER TABLE books ADD INDEX idx_search_combined (title(50), author(50), isbn(20)) COMMENT 'For combined search queries';

-- ✅ Index for book copies relationship
ALTER TABLE book_copies ADD INDEX idx_book_id (book_id) COMMENT 'For faster copy lookups';
ALTER TABLE book_copies ADD INDEX idx_availability (availability) COMMENT 'For availability filtering';
ALTER TABLE book_copies ADD INDEX idx_book_availability (book_id, availability) COMMENT 'For copy status lookups';

-- ✅ Indexes for category and subject mapping
ALTER TABLE book_category_map ADD INDEX idx_book_id (book_id) COMMENT 'For category lookups';
ALTER TABLE book_category_map ADD INDEX idx_category_id (category_id) COMMENT 'For category filtering';
ALTER TABLE book_category_map ADD INDEX idx_combined (book_id, category_id) COMMENT 'For map queries';

ALTER TABLE book_subject_map ADD INDEX idx_book_id (book_id) COMMENT 'For subject lookups';
ALTER TABLE book_subject_map ADD INDEX idx_subject_id (subject_id) COMMENT 'For subject filtering';
ALTER TABLE book_subject_map ADD INDEX idx_combined (book_id, subject_id) COMMENT 'For map queries';

-- =====================================================
-- Verification Queries (Run these to verify indexes)
-- =====================================================

-- View all indexes on books table
SHOW INDEX FROM books;
SHOW INDEX FROM book_copies;
SHOW INDEX FROM book_category_map;
SHOW INDEX FROM book_subject_map;

-- Check table sizes
SELECT 
  table_name, 
  ROUND(((data_length) / 1024 / 1024), 2) AS data_size_mb,
  ROUND(((index_length) / 1024 / 1024), 2) AS index_size_mb,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS total_size_mb 
FROM information_schema.TABLES 
WHERE table_schema = 'libsystem5' AND table_name IN ('books', 'book_copies', 'book_category_map', 'book_subject_map')
ORDER BY data_length DESC;

-- =====================================================
-- Maintenance (Run monthly)
-- =====================================================

-- Optimize tables (defragments the tables)
-- OPTIMIZE TABLE books;
-- OPTIMIZE TABLE book_copies;
-- OPTIMIZE TABLE book_category_map;
-- OPTIMIZE TABLE book_subject_map;

-- Analyze table statistics (helps query optimizer)
-- ANALYZE TABLE books;
-- ANALYZE TABLE book_copies;
-- ANALYZE TABLE book_category_map;
-- ANALYZE TABLE book_subject_map;

-- =====================================================
-- Notes
-- =====================================================
-- 1. Indexes use extra disk space (usually 20-30% of data size)
-- 2. Indexes speed up SELECT queries but slow down INSERT/UPDATE/DELETE
-- 3. For this application (mostly reads), indexes are worth it
-- 4. Monitor with: SHOW PROCESSLIST; during heavy usage
-- 5. Check slow query log: SET GLOBAL slow_query_log = 'ON';
