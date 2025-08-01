## About APP

A simple social feed app built with **Laravel**, **Jetstream (Inertia + Vue 3)**, and **SQLite**.  
Users can create posts, like them, and leave comments. Only post owners can edit or delete their posts.


## 🚀 How to Run the Project (Step-by-Step)

### 1. Unzip or Clone the Repository

If you've received a `.zip` file, extract it to your project directory. 

## Install PHP Dependencies

composer install


## Install Node Dependencies

npm install


## Create .env File

cp .env.example .env

## Generate Application Key

php artisan key:generate


## Run Migrations & Seeders

php artisan migrate --seed

if system ask
- The SQLite database configured for this application does not exist: database/database.sqlite. 
Hit Yes

### This will create:
- 5 demo users
- Each user with 5 posts
- Random likes and comments

Note: This app uses SQLite — no database setup required.
A database file (database/database.sqlite) will be auto-created.

## Test Users you will get
- alice@example.com
- bob@example.com
- charlie@example.com
- david@example.com
- eve@example.com

Password of all the user is 'password'

## Compile & Start the App

composer run dev

## The Reverb server start Artisan command For realtime likes update

php artisan reverb:start

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
