-- ============================================================
-- ATENÇÃO: APENAS PARA TESTES LOCAIS / LOAD TESTING.
-- Cria contas ativas com a password conhecida "password"
-- (hash bcrypt público). NUNCA executar em PRODUÇÃO.
-- ============================================================

SET @start := COALESCE((
  SELECT MAX(CAST(SUBSTRING(username, 10) AS UNSIGNED))
  FROM users
  WHERE username REGEXP '^loadtest_[0-9]+$'
), 0);

INSERT INTO users (
  username,
  email,
  email_verified_at,
  password,
  avatar_id,
  bio,
  is_active,
  last_login_at,
  created_at,
  updated_at
)
SELECT
  CONCAT('loadtest_', LPAD(@start + n, 4, '0')),
  CONCAT('loadtest+', LPAD(@start + n, 4, '0'), '@example.test'),
  NOW(),
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
  'hero',
  CONCAT('Perfil de teste #', @start + n, ' para validar listagens e paginacao.'),
  1,
  DATE_SUB(NOW(), INTERVAL MOD(n, 30) DAY),
  DATE_SUB(NOW(), INTERVAL n DAY),
  NOW()
FROM (
  SELECT ROW_NUMBER() OVER (ORDER BY TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME) AS n
  FROM information_schema.COLUMNS
  LIMIT 300
) AS seq;

SELECT
  COUNT(*) AS total_users,
  SUM(username LIKE 'loadtest_%') AS loadtest_users
FROM users;
