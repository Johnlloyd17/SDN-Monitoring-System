DROP TABLE IF EXISTS mysql.proxies_priv;
CREATE TABLE mysql.proxies_priv (
  Host char(60) NOT NULL DEFAULT '',
  User char(32) NOT NULL DEFAULT '',
  Proxied_host char(60) NOT NULL DEFAULT '',
  Proxied_user char(32) NOT NULL DEFAULT '',
  With_grant bool NOT NULL DEFAULT 0,
  Grantor varchar(93) NOT NULL DEFAULT '',
  Timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (Host, User, Proxied_host, Proxied_user),
  KEY Grantor (Grantor)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='User proxy privileges';
FLUSH PRIVILEGES;