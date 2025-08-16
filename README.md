# Whilesmart Laravel Model Configuration Package

This Laravel package provides a complete configuration solution ready to be integrated into your application.

## Features

* **Ready-to-use  endpoints:**
* **OpenAPI documentation:** Automatically generated documentation using PHP attributes.
* **Configuration file:** Easily customize settings.
* **Laravel agnostic considerations:** designed with future framework agnosticism in mind.

## Development

This package includes a Docker development environment and Makefile for easy development.

### Quick Start

```bash
# Start the development environment
make up

# Install dependencies
make install

# Run tests
make test

# Run code formatter
make pint

# Show all available commands
make help
```

### Available Make Commands

- `make up` - Start Docker containers
- `make down` - Stop Docker containers
- `make install` - Install dependencies
- `make test` - Run tests
- `make pint` - Run Laravel Pint code formatter
- `make lint` - Alias for pint
- `make fresh` - Fresh start (down, up, install)
- `make setup` - Complete setup with tests
- `make check` - Run all checks (formatting + tests)
- `make shell` - Access container shell

## Installation

### 1. Require the package

   ```bash
   composer require whilesmart/laravel-model-configuration
   ```

### 2. Publish the configuration and migrations:

You do not need to publish the migrations and configurations except if you want to make modifications. You can choose to
publish
the migrations, routes, controllers separately or all at once.

#### 2.1 Publishing only the routes

Run the command below to publish only the routes.

```bash
php artisan vendor:publish --tag=model-configuration-routes
php artisan migrate
```

The routes will be available at `routes/model-configuration.php`. You should `require` this file in your `api.php` file.

```php
    require 'model-configuration.php';
```

#### 2.2 Publishing only the migrations

+If you would like to make changes to the migration files, run the command below to publish only the migrations.

```bash
php artisan vendor:publish --tag=model-configuration-migrations
php artisan migrate
```

The migrations will be available in the `database/migrations` folder.

#### 2.3 Publish only the controllers

By default the controllers assign the device to the currently logged in user. If you would like to assign the device to
another model, you can publish the controllers and make the necessary changes to the published file. <br/>
To publish the controllers, run the command below

```bash
php artisan vendor:publish --tag=model-configuration-controllers
php artisan migrate
```

The controllers will be available in the `app/Http/Controllers` directory.
Finally, change the namespace in the published controllers to your namespace.

#### Note: Publishing the controllers will also publish the routes. See section 2.1

#### 2.4 Publish everything

To publish the migrations, routes and controllers, you can run the command below

```bash
php artisan vendor:publish --tag=model-configuration
php artisan migrate
```

#### Note: See section 2.1 above to make the routes accessible

### 3. Model Relationships

We have implemented a Trait `Configurable` that handles relationships. If your model has configuration, simply use the
`Configurable` trait in your model definition.

```php
use Whilesmart\ModelConfiguration\Traits\Configurable
class MyModel {
 use Configurable;
}
 
```

You can call `yourModel->configurations()` to get the list of configuration tied to the model

```php
$model = new MyModel();
$model->configurations();
```

The `Configurable` trait also has the `getConfigurationssAttribute()` method. If you want to append the configuration to the model response, simply add `configuration` to your model's $appends

```php
use Whilesmart\ModelConfiguration\Traits\Configurable;
class MyModel {
 use Configurable;
 
 protected $appends = ['configurations'];
}

```

The following additional methods are available also available in the `Configurable` trait
1. `getConfig()` gets the full details of a particular config. It returns a `Configuration` object
```php

$model = new MyModel();
$config = $model->getConfig('default_wallet');
$config_id = $config->id;
```
2. `getConfigValue()` gets the value of a particular config. It also ensures the value is returned in the correct type
```php

$model = new MyModel();
$config_value = $model->getConfigValue('default_wallet');
```

3. `getConfigType()` gets the type of a particular config. It returns a `ConfigValueType` enum
```php

$model = new  MyModel();
$config_type = $model->getConfigType('default_wallet');
```
4. `setConfigValue()` sets/updates the value of a particular config. It returns a `Configuration` object
```php

$model = new MyModel();
$config= $model->setConfigValue('default_wallet',1, ConfigValueType::Integer);
```

## Usage

After installation, the following API endpoints will be available:

* **POST /configuration:** Registers a new device linked to the current logged in user.
* **Get /configuration:** Retrieves all configuration linked to the current logged in user.
* **PUT /configuration/{id}:** Updates the device information.
* **DELETE /configuration/{id}:** Deletes a device from the database.
* **OpenAPI Documentation:** Accessible via a route that your OpenAPI package defines.

**Example  Request:**

```json
{
  "value":"unique_token_string",
  "type":"string"
}
