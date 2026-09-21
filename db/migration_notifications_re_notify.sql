-- Re-notify duration: dismissals expire after a chosen duration and the alert reappears.
-- re_notify_minutes = minutes to keep the alert hidden. Default 1440 (24h).
ALTER TABLE notification_dismissals
    ADD COLUMN re_notify_minutes INT NOT NULL DEFAULT 1440 AFTER dismissed_at;