1. Clone the Repository
   git clone https://github.com/mubeen135/url-shortener.git
   cd url-shortener

2. Install PHP Dependencies
   composer install

3. Configure Environment
   cp .env.example .env
   php artisan key:generate

4. Update .env File
   APP_NAME="URL Shortener"
   APP_ENV=local
   APP_KEY=base64:...
   APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

# DB_DATABASE=database/database.sqlite (uncomment if using SQLite)

# For MySQL:

# DB_CONNECTION=mysql

# DB_HOST=127.0.0.1

# DB_PORT=3306

# DB_DATABASE=url_shortener

# DB_USERNAME=root

# DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

5. Run migrations
   php artisan migrate

6. Create Storage Link
   php artisan storage:link

7. Install NPM Dependencies (Optional)
   npm install
   npm run build
