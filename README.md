# POS

A point-of-sale (POS) web app written in PHP, with a MySQL / MariaDB database.

## What is in this repo

| Path | Contents |
|---|---|
| `index.php` | Entry point. Sets the time zone (`Asia/Karachi`), loads `config.php` and starts the app |
| `config.php` | URLs and folder paths. All URLs are built on a folder named `ifg`, so the app is expected to be served from `/ifg/` |
| `admin/` | The application: `controller/`, `model/`, `view/` and `language/` |
| `system/` | Core code: `engine/`, `library/`, `helper/`, `database/`, `config/`, `cache/` and `logs/` |
| `assets/` | Front-end files: `bootstrap/`, `dist/` and `plugins/` |
| `database/` | SQL dumps (`ifg.sql`, `ifg_master.sql`) and the schema changes made later (`Alter-Queries-*.txt`, October and November 2023) |
| `docs/` | Screenshots |
| `image/` | Images used by the app |

## Requirements

The SQL dump in `database/ifg.sql` was exported with PHP 7.4 and MariaDB 10.4, so use those versions or newer. You also need a web server such as Apache or Nginx.

## Setup

1. Copy the project to your web server in a folder named `ifg`.
2. Create a database and import `database/ifg.sql`.
3. Open `http://localhost/ifg/` in the browser.

## Screenshots

![Screenshot 1](docs/Screenshot%20%285%29.png)
![Screenshot 2](docs/Screenshot%20%286%29.png)
![Screenshot 3](docs/Screenshot%20%287%29.png)
![Screenshot 4](docs/Screenshot%20%288%29.png)
![Screenshot 5](docs/Screenshot%20%289%29.png)
![Screenshot 6](docs/Screenshot%20%2810%29.png)
