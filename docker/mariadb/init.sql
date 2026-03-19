-- Runs on first container startup only (empty data volume)
-- Actual DB is imported via: docker compose exec -T mariadb mysql -udrupal -pdrupal drupal < dump.sql
SET NAMES utf8mb4;
