<?php 

use libs\app\Route as Route;

Route::register('@testederota', 'test');
Route::register('admin@clients', 'admin/clients');
Route::register('admin@config', 'admin/config');

#!
Route::register('@auth', 'auth');
Route::register('client@clients', 'client/clients');
Route::register('client@files', 'client/files');
Route::register('@test', 'test');
