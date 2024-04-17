<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Columns
    |--------------------------------------------------------------------------
    |
    | These are the columns to be displayed in the user table.
    |
    */
    // Define the columns to be displayed in the user table package.
    // 'users_columns' => [
    //     'id',
    //     'name',
    //     'email',
    //     'email_verified_at',
    // ],

    // 'User_unique_column' => 'email',  // Unique column format: ModelName_unique_column
    // 'User_SessionKey' => 'user_columns', // Session key format: ModelName_SessionKey
    // 'User_has_image' => 'image', // Session key format: ModelName_SessionKey
    // 'User_has_edit_option' => false,
    // 'User_has_create_option' => false,

    /*
    |--------------------------------------------------------------------------
    | Rows Per Page
    |--------------------------------------------------------------------------
    |
    | These are the options for the number of rows per page in pagination.
    |
    */
    // Define the options for the number of rows per page in pagination.
    'rowsPerPage' => [10, 20, 30, 50, 100, 200],
];

/*
|--------------------------------------------------------------------------
| Warning
|--------------------------------------------------------------------------
|
| We have not used the blacklist column, so please manage confidence columns
| from your side to ensure that those columns are not included in this configuration.
|
*/
