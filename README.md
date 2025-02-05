# Telegram Crypto Price Alert Bot

A Telegram bot designed to help cryptocurrency traders and enthusiasts stay updated on price movements. The bot allows users to set custom price alerts for their favorite cryptocurrency pairs and notifies them in real-time when the specified conditions are met. It fetches real-time price data from **Binance**, one of the largest cryptocurrency exchanges in the world.

---

## Features

- **Real-Time Price Alerts**: Set alerts for specific cryptocurrency pairs (e.g., `BTC/USDT`, `ETH/BTC`) and get notified when the price crosses your specified threshold.
- **Custom Conditions**: Set conditions like `>` (greater than) or `<` (less than) a target price.
- **User-Friendly Commands**: Simple and intuitive Telegram commands to manage alerts.
- **Rate Limiting**: Ensures compliance with Telegram's API rate limits to avoid being blocked.
- **Database Backed**: Stores user alerts in a MySQL database for persistence.

---

## How It Works

1. **Data Fetching**: The bot fetches real-time cryptocurrency prices from Binance's public API (`https://api.binance.com/api/v3/ticker/price`).
2. **Alert Management**: Users can set, remove, and view their alerts using simple Telegram commands.
3. **Cron Job**: A background script (`cronjob.php`) runs periodically to check if any alerts have been triggered based on the latest prices.
4. **Notifications**: If an alert condition is met, the bot sends a notification to the user via Telegram.

---

## Commands

- `/setalert <symbol> <condition> <price>`: Set a price alert for a cryptocurrency pair.
  - Example: `/setalert BTC/USDT > 50000` (Notifies you when BTC/USDT price exceeds $50,000).
- `/removealert`: Remove all active alerts for the user.
- `/getalert`: View all active alerts for the user.

---

## Setup Instructions

### Prerequisites

1. **Telegram Bot Token**: Create a bot using [BotFather](https://core.telegram.org/bots#botfather) and get the bot token.
2. **MySQL Database**: Set up a MySQL database and create the required tables (see [Database Setup](#database-setup)).
3. **Web Server**: Host the bot on a web server with PHP support.
4. **Cron Job**: Set up a cron job to run the `cronjob.php` script periodically.
5. **Save pairs**: run save_pairs.php once to fetch all pairs into database
### Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/your-username/telegram-crypto-price-bot.git
   cd telegram-crypto-price-bot