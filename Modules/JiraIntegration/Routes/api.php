<?php

use Illuminate\Routing\Router;

Route::middleware('auth:api')->group(static function (Router $router) {
    $router->get('/settings', 'SettingsController@get')->name('settings.get');
    $router->post('/settings', 'SettingsController@set')->name('settings.set');
    $router->get('/companysettings', 'CompanySettingsController@get')->name('companysettings.get');
    $router->post('/companysettings', 'CompanySettingsController@set')->name('companysettings.set');
});
