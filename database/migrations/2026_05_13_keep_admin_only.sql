USE defaultdb;

SET @admin_id := (
  SELECT id
  FROM users
  WHERE email = 'schoolyoro@gmail.com'
  LIMIT 1
);

UPDATE items
SET reporter_id = @admin_id
WHERE reporter_id <> @admin_id;

UPDATE claims
SET user_id = NULL
WHERE user_id IS NOT NULL
  AND user_id <> @admin_id;

UPDATE claims
SET reviewed_by = NULL
WHERE reviewed_by IS NOT NULL
  AND reviewed_by <> @admin_id;

UPDATE activity_logs
SET user_id = @admin_id
WHERE user_id IS NOT NULL
  AND user_id <> @admin_id;

DELETE FROM users
WHERE id <> @admin_id;
