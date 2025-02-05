CREATE TABLE `alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` bigint NOT NULL,
  `symbol` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `conditionstate` enum('>', '<') NOT NULL, -- Use ENUM for strict validation
  `price` decimal(18,8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`) -- Add an index for chat_id for faster lookups
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `available_pairs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `symbol` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `symbol` (`symbol`) -- Ensure no duplicate symbols
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;