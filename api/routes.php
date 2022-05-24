<?php 

use libs\app\Route as Route;

Route::register('@testederota', 'test');
Route::register('admin@clients', 'admin/clients');
Route::register('admin@config', 'admin/config');
Route::register('admin@permissions', 'admin/permissions');

#!
Route::register('@auth', 'auth');
Route::register('client@files', 'client/files');
Route::register('client@users', 'client/users');
Route::register('@test', 'test');
