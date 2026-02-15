-- =====================================================
-- E-BOOK COLLECTION OPTIMIZATION - DATABASE INDEXES
-- =====================================================
-- Run these SQL commands to optimize calibre_books table
-- for 10,000+ records
--
-- Time to run: 2-5 minutes (depending on current data)
-- Performance improvement: 10-100x faster queries
--
-- Execute in phpMyAdmin or MySQL CLI:
-- mysql -u root -p libsystem5 < EBOOK_INDEXES.sql
-- =====================================================

USE libsystem5;

-- ✅ Add individual indexes for common search fields
ALTER TABLE calibre_books ADD INDEX idx_identifiers (identifiers(50)) COMMENT 'For fast ISBN/identifier search';
ALTER TABLE calibre_books ADD INDEX idx_author (author(100)) COMMENT 'For fast author search';
ALTER TABLE calibre_books ADD INDEX idx_title (title(100)) COMMENT 'For fast title search';
ALTER TABLE calibre_books ADD INDEX idx_tags (tags(100)) COMMENT 'For fast tag filtering';
ALTER TABLE calibre_books ADD INDEX idx_created_at (created_at) COMMENT 'For sorting by creation date';

-- ✅ Composite index for common combined searches
ALTER TABLE calibre_books ADD INDEX idx_combined_search (identifiers(50), author(50), title(50), tags(50)) COMMENT 'For combined multi-field search';

-- ✅ Optional: FULLTEXT index for advanced text search (uncomment if needed)
-- ALTER TABLE calibre_books ADD FULLTEXT INDEX ft_search (identifiers, author, title, tags) COMMENT 'For FULLTEXT search queries';

-- =====================================================
-- Verification Queries (Run these to verify indexes)
-- =====================================================

-- View all indexes on calibre_books table
SHOW INDEX FROM calibre_books;

-- Check table size and index size
SELECT 
  table_name, 
  ROUND(((data_length) / 1024 / 1024), 2) AS data_size_mb,
  ROUND(((index_length) / 1024 / 1024), 2) AS index_size_mb,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS total_size_mb 
FROM information_schema.TABLES 
WHERE table_schema = 'libsystem5' AND table_name = 'calibre_books';

-- Analyze query performance (EXPLAIN)
-- EXPLAIN SELECT * FROM calibre_books WHERE title LIKE 'search%' LIMIT 20;
-- Look for "Using index" or "Using where; Using index" in the results

-- =====================================================
-- Maintenance (Run monthly)
-- =====================================================

-- Optimize table (defragments the table)
-- OPTIMIZE TABLE calibre_books;

-- Analyze table statistics (helps query optimizer)
-- ANALYZE TABLE calibre_books;

-- Rebuild fragmented indexes
-- REPAIR TABLE calibre_books;

-- =====================================================
-- Notes
-- =====================================================
-- 1. Indexes use extra disk space (usually 20-30% of data size)
-- 2. Indexes speed up SELECT queries but slow down INSERT/UPDATE/DELETE
-- 3. For this application (mostly reads), indexes are worth it
-- 4. Monitor with: SHOW PROCESSLIST; during heavy usage
-- 5. Check slow query log: SET GLOBAL slow_query_log = 'ON';
