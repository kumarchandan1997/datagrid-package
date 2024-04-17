# Custom DataGrid

Custom DataGrid is a powerful Laravel package that simplifies the process of creating dynamic grids with minimal code. With features like Bootstrap 5 compatibility, CSV and TSV exporting, sorting, searching, and inline or bulk actions, managing tabular data becomes a breeze. This README will guide you through the installation and configuration process, ensuring you harness the full potential of Custom DataGrid.

## Table of Contents

- [Custom DataGrid](#custom-datagrid)
  - [Table of Contents](#table-of-contents)
  - [About this project](#about-this-project)
  - [Compatibility](#compatibility)
  - [Installation](#installation)
    - [Dependencies](#dependencies)
  - [Usage](#usage)
    - [A Simple Example](#a-simple-example)
  - [Using Configuration for Data Grid in Laravel](#using-configuration-for-data-grid-in-laravel)
    - [1. Define Unique Session Key](#1-define-unique-session-key)
    - [2. Retrieve Selected Columns](#2-retrieve-selected-columns)
    - [3. Set Default Columns if Session Data is Not Set](#3-set-default-columns-if-session-data-is-not-set)
    - [4. Get All Available Columns](#4-get-all-available-columns)
    - [5. Render Data Grid](#5-render-data-grid)
    - [6. Pass Data to View](#6-pass-data-to-view)
  - [Configuration for Multiple Tables](#configuration-for-multiple-tables)
    - [1. Define Table Columns](#1-define-table-columns)
    - [2. Specify Unique Column](#2-specify-unique-column)
    - [3. Set Session Key](#3-set-session-key)
    - [4. Enable or Disable Edit Option](#4-enable-or-disable-edit-option)

## About this project

Custom DataGrid offers a streamlined approach to building robust grids for your Laravel application. Its simplicity in setup and flexibility in customization make it a preferred choice for developers aiming to enhance their data management capabilities.

## Compatibility

Custom DataGrid is fully compatible with Laravel 10, ensuring seamless integration with your existing projects.

## Installation

To begin using Custom DataGrid, follow these simple steps:

1. **Add Dependency**:You can install the package via Composer. Run the following command in your terminal:

    ```bash
    composer require bytestechnolabs/datagrid 
    ```

2. **Service Provider**: Add the Custom DataGrid service provider to your Laravel application's `config/app.php` file:

   ```php
   Datagrid\DatagridServiceProvider::class,
   ```

3. **Publish Configuration**: Execute `php artisan vendor:publish --provider="Datagrid\DatagridServiceProvider"` to publish the configuration files.

4. **Autoload**: Refresh the Composer autoload files by running `composer dump-autoload`.

5. **Include Assets**: Include the necessary JavaScript and CSS dependencies in your HTML to enable Custom DataGrid functionality.

### Dependencies

Make sure to have the following dependencies installed and configured:

- Bootstrap 5
- jQuery

## Usage

### A Simple Example

Let's walk through a basic example of implementing Custom DataGrid in your Laravel application.

```php
use Datagrid\Facades\DataGridFacade;

public function index()
{
    // Define the unique session key
    $sessionKey = config('datagrid.User_SessionKey');
    // Retrieve the selected columns from the session
    $columns = Session::get($sessionKey);
    // Use default columns if session data is not set
    if ($columns === null) {
        $columns = config('datagrid.users_columns');
        Session::put($sessionKey, $columns);
    }

    // Get all available columns
    $columnsAll = config('datagrid.users_columns');

    // Render the data grid
    $dataGrid = DataGridFacade::model(User::class)
        ->columns($columns)
        ->searchColumns($columns)
        ->columnsAll($columnsAll)
        ->paginate(10);

    return view('test', ['dataGrid' => $dataGrid]);
}
```

Ensure to replace `User::class` with your model in the `DataGridFacade::model()` call.

---

## Using Configuration for Data Grid in Laravel

To utilize the configuration for the data grid in Laravel, follow the steps outlined below:

### 1. Define Unique Session Key

In your controller method (`index()` in this case), retrieve the unique session key from the configuration file. This key is used to manage sessions related to the specific table.

```php
// Define the unique session key
$sessionKey = config('datagrid.User_SessionKey');
```

### 2. Retrieve Selected Columns

Retrieve the selected columns from the session using the unique session key obtained in the previous step.

```php
// Retrieve the selected columns from the session
$columns = Session::get($sessionKey);
```

### 3. Set Default Columns if Session Data is Not Set

If the session data for selected columns is not set, use the default columns specified in the configuration file and store them in the session.

```php
// Use default columns if session data is not set
if ($columns === null) {
    $columns = config('datagrid.users_columns');
    Session::put($sessionKey, $columns);
}
```

### 4. Get All Available Columns

Retrieve all available columns from the configuration file.

```php
// Get all available columns
$columnsAll = config('datagrid.users_columns');
```

### 5. Render Data Grid

Render the data grid using the Laravel Data Grid package, specifying the selected columns, search columns, and all available columns. Optionally, paginate the results.

```php
// Render the data grid
$dataGrid = DataGridFacade::model(User::class)
    ->columns($columns)
    ->searchColumns($columns)
    ->columnsAll($columnsAll)
    ->paginate(10);
```

### 6. Pass Data to View

Pass the data grid to the view for rendering.

```php
return view('test', ['dataGrid' => $dataGrid]);
```

By following these steps, you can effectively utilize the configuration for the data grid in your Laravel application.

---

## Configuration for Multiple Tables

To configure multiple tables and specify their unique columns and session keys, follow these steps:

### 1. Define Table Columns

In the `config/datagrid.php` file, specify the columns for each table under the `users_columns` array. Each table should have its own array containing the column names.

Example:
```php
'users_columns' => [
    'id',
    'name',
    'email',
    'email_verified_at',
],
```

### 2. Specify Unique Column

Define the unique column for each table using the format `ModelName_unique_column`. This configuration is used to validate uniqueness when updating records.

Example:
```php
'User_unique_column' => 'email',
```

### 3. Set Session Key

Assign a unique session key for each table using the format `ModelName_SessionKey`. This key is used to manage sessions related to the specific table.

Example:
```php
'User_SessionKey' => 'user_columns',
```

### 4. Enable or Disable Edit Option

Specify whether the table has an edit option available. Set to `true` if the table allows editing, otherwise set to `false`.

Example:
```php
'User_has_edit_option' => false,
```

Repeat these steps for each table you want to configure. Ensure that the configuration is accurate and consistent across all tables.

---
## Blade File Example

To include the package's view in your Laravel application, follow these steps:

1. Create a new blade file (e.g., `datagrid.blade.php`) in your `resources/views` directory.

2. Add the following code to the blade file:

```php
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @include('datagrid::layouts.style')
</head>

<body>
    {!! $dataGrid->render() !!}
</body>

</html>
```

3. Make sure to include all the necessary styles and render the data grid using the `render()` method provided by the package.

4. You can then use this blade file in your application to display the data grid provided by the package.

The result will be like this:

![datagrid](https://github.com/kumarchandan1997/datagrid-package/assets/89054724/78f2e72d-7282-4619-9683-b05f4a0b6722)

