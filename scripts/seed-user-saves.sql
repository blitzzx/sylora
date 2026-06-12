/* Get-Content .\scripts\seed-user-saves.sql -Raw | docker exec -i sylora-db mysql -uroot -proot sylora */

INSERT INTO saves (
  user_id,
  slot,
  player_name,
  save_data,
  level,
  hp,
  hp_total,
  xp,
  xp_req,
  damage,
  chapter,
  story_progress,
  last_saved,
  created_at
)
SELECT
  user_id,
  1,
  player_name,
  JSON_OBJECT(
    'player_name', player_name,
    'stats', JSON_OBJECT(
      'lvl', level,
      'hp', hp,
      'hp_total', hp_total,
      'xp', xp,
      'xp_req', xp_req,
      'damage', damage,
      'story_progress', story_progress,
      'save_rm', save_room
    )
  ),
  level,
  hp,
  hp_total,
  xp,
  xp_req,
  damage,
  chapter,
  story_progress,
  last_saved,
  last_saved
FROM (
  SELECT
    user_id,
    player_name,
    level,
    ROUND(hp_total * hp_ratio, 1) AS hp,
    hp_total,
    FLOOR(xp_req * xp_ratio) AS xp,
    xp_req,
    ROUND(3 + (level * 1.6) + damage_bonus, 1) AS damage,
    LEAST(100, (level * 5) + story_bonus) AS story_progress,
    save_room,
    chapter,
    DATE_SUB(NOW(), INTERVAL saved_days DAY) AS last_saved
  FROM (
    SELECT
      id AS user_id,
      LEFT(username, 32) AS player_name,
      level,
      100 + (level * 12) AS hp_total,
      level * 140 AS xp_req,
      0.55 + (RAND() * 0.45) AS hp_ratio,
      RAND() AS xp_ratio,
      RAND() * 4 AS damage_bonus,
      FLOOR(RAND() * 8) AS story_bonus,
      FLOOR(RAND() * 21) AS saved_days,
      CASE room_idx
        WHEN 0 THEN 'Thalassos'
        WHEN 1 THEN 'Thalassos_Cave'
        WHEN 2 THEN 'Thalassos_Boss'
        WHEN 3 THEN 'Helion'
        ELSE 'Zephyria'
      END AS save_room,
      CASE room_idx
        WHEN 0 THEN 'Ato I: Ilha de Thalassos'
        WHEN 1 THEN 'Ato I: Gruta de Thalassos'
        WHEN 2 THEN 'Ato I: Templo de Pelagion'
        WHEN 3 THEN 'Ato II: As Cinzas de Helion'
        ELSE 'Ato III: O Veu dos Ventos'
      END AS chapter
    FROM (
      SELECT
        id,
        username,
        FLOOR(6 + (RAND() * 15)) AS level,
        FLOOR(RAND() * 5) AS room_idx
      FROM users
    ) AS users_seed
  ) AS stats_seed
) AS save_seed
ON DUPLICATE KEY UPDATE
  player_name = VALUES(player_name),
  save_data = VALUES(save_data),
  level = VALUES(level),
  hp = VALUES(hp),
  hp_total = VALUES(hp_total),
  xp = VALUES(xp),
  xp_req = VALUES(xp_req),
  damage = VALUES(damage),
  chapter = VALUES(chapter),
  story_progress = VALUES(story_progress),
  last_saved = VALUES(last_saved);

SELECT
  COUNT(*) AS total_saves,
  COUNT(DISTINCT user_id) AS users_with_saves,
  MIN(level) AS min_level,
  MAX(level) AS max_level
FROM saves;
