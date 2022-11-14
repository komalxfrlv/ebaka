# Installation

1. Clone this repo

```
$ git clone -/-
```

2. Install composer packages

```
$ cd -/-
$ composer update
```

3. Create and setup .env file

```
make a copy of .env.example
$ copy .env.example .env
$ php artisan key:generate
put database credentials in .env file
$ php artisan jwt:secret
```

4. Migrate and insert records

```
$ php artisan migrate
```

5. Run App

```
$ php artisan serve
```
