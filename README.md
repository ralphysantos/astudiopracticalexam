# Setup Instructions for Existing Laravel API Project with Passport

Follow these steps to set up an existing Laravel API project with Passport:

1. **Clone the Repository**
    ```bash
    git clone https://github.com/ralphysantos/astudiopracticalexam.git
    cd <repository-directory>
    ```

2. **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3. **Environment Configuration**
    - Copy the `.env.example` file to `.env`
    - Update the `.env` file with your database and other configurations

4. **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5. **Run Migrations**
    ```bash
    php artisan migrate
    ```

6. **Install Passport**
    ```bash
    php artisan passport:install
    ```

7. **Passport Configuration**
    - Add `Laravel\Passport\HasApiTokens` trait to your `User` model
    - In `config/auth.php`, set the `api` driver to `passport`
    ```php
    'guards' => [
         'api' => [
              'driver' => 'passport',
              'provider' => 'users',
         ],
    ],
    ```

8. **Run the Application**
    ```bash
    php artisan serve
    ```

9. **Test Credentials**
    - `email`: `test3@test.com`
    - `password` : `Test1234`