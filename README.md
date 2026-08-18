# Resulty

University composite result processing: upload semester Excel sheets, calculate TCU / TQP / GPA, merge sessions, and export degree summaries.

## Requirements

- **PHP 8.2+** (8.2 and 8.3 both work)
- Composer
- Node.js 18+
- PHP extensions: `mbstring`, `xml`, `zip`, `gd`, `fileinfo`, `pdo_sqlite`

## Setup

```bash
git clone <this-repo-url>
cd resulty

composer setup
php artisan serve
```

`composer setup` copies `.env.example` to `.env`, generates the app key, creates the SQLite database, runs migrations, and builds the frontend.

Then open http://127.0.0.1:8000

If you prefer to run the steps yourself:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Do **not** copy `vendor/`, `node_modules/`, or `.env` from another machine. Install dependencies locally with Composer and npm so they match your PHP version.

## Sample files

Templates live in `storage/app/samples/`:

- `semester-composite-sample.xlsx`
- `session-summary-sample.xlsx`
- `degree-summary-sample.docx`
