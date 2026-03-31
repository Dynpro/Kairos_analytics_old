# 🚀 Server Deployment Guide (KAIROS Project)

To handle the transition from your local environment to the server tomorrow, you need to ensure the database on the server is correctly populated with the updated dashboard information. Follow the steps below for a smooth deployment.

---

## 📅 Part 1: Deployment Checklist

- [ ] Push code to the server (Git or FTP).
- [ ] Configure server's **`.env`** file.
- [ ] Export local database and import to server.
- [ ] Run **`php run_insert.php`** once to populate dashboards.
- [ ] Build the frontend with the correct **`G_URL`**.

---

## 🔧 Step 1: Export Local Database
Since your local MySQL currently contains the 11 core tables and the latest dashboard mappings, you should export the full database to take it with you.

1. Open your local terminal.
2. Run the following command:
   ```bash
   mysqldump -u kairos_user -p kairos > kairos_full_backup.sql
   ```
   *(Enter password: `Kairos@123` when prompted)*.

---

## 🌐 Step 2: Backend Configuration on Server
Once the project is on the server, update your environment settings.

1. Locate the **`.env`** file in your backend root (`hca_api-main`).
2. Update the fields to match your server's MySQL settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1  # Or the server's DB IP
   DB_PORT=3306
   DB_DATABASE=your_server_db_name
   DB_USERNAME=your_server_db_user
   DB_PASSWORD=your_server_db_password
   ```

---

## 🗄️ Step 3: Synchronize Database on Server
Move the `kairos_full_backup.sql` file to the server and import it.

1. **Import the schema**:
   ```bash
   mysql -u your_server_user -p your_server_db_name < kairos_full_backup.sql
   ```
2. **Synchronize Dashboards**:
   To ensure all 79 dashboards and their Looker IDs are perfectly synced, run the insertion script:
   ```bash
   php run_insert.php
   ```
   *This will truncate the tables and insert all 79 dashboards exactly as they are currently set up locally.*

3. **Verify**:
   Run the SQL command on the server to check the row count:
   ```sql
   select count(*) from users_dasboards_mapping;
   ```
   *(Should return 79)*.

---

## 💻 Step 4: Frontend API Connection
Ensure the frontend knows where the new backend is located.

1. Open **`src/app/components/commonConfig.js`** (or your specific config file).
2. Update the API base URL:
   ```javascript
   export const G_URL = "https://your-server-api-domain.com/api/";
   ```
3. Generate production files:
   ```bash
   npm run build
   ```

---

## 📌 Note on "Missing Tables"
Currently, your local MySQL shows 11 tables (Core Dashboard & User Access). 
- **If the app works locally**: These 11 tables are sufficient for your current features.
- **Other Tables**: Tables like `phm`, `reports`, or `client_folder_mapping` might be missing if those modules haven't been initialized or used locally. Using the **`mysqldump`** method above ensures that **everything** you have locally is moved to the server, and **`run_insert.php`** ensures the data is flawless.
