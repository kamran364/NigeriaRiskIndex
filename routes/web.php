<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\statepopgeom;
use Illuminate\Http\Request;

Route::get('ttest', 'IncidentController@get_dashboard_data');
Route::get('get-authors-list', 'IncidentController@GetAuthorsList')->name('get-authors-list');
Route::get('AddData', 'UserController@test')->name('test');
Auth::routes();
Route::get('/hassan', function () {
//    dd(auth()->check());
    return view('welcome');
});


Route::get('logout', 'Auth\LoginController@logout');
Route::post('/login', 'UserController@login')->name('login');
Route::get('/home', 'HomeController@index')->name('home');
//Route::get('/profile', 'HomeController@profile')->name('profile');
Route::post('/profile', 'HomeController@save_profile')->name('save_profile');
//Route::post('/update_profile', 'HomeController@update_profile')->name('update_profile');

// Data Officer - Routes
Route::get('/add-incidents', 'IncidentController@index')->name('add-incidents');


Route::get('/get-reports/{year?}', 'ReportsController@yearly_report')->name('get_reports');
Route::get('/update-reports', 'ReportsController@update')->name('update_reports');
Route::get('get-manage-advisory', 'IncidentController@getManageAdvisory')->name('get-manage-advisory');
//News
Route::get('reset_password/{t}', 'UserController@reset_password')->name('reset_password');
Route::get('get-state-dashboard', 'IncidentController@GetStateDashboard')->name('get-state-dashboard');

// Admin Routes
Route::get('admin', array('as' => 'admin', function () {
    die('Hassan');
    return View::make('welcome');
}));



Route::get('get-roles', 'RoleController@get_role'); //Get Roles list
Route::get('get_permission', 'RoleController@get_permission')->name('get_permission'); //Get Roles list
Route::get('get_role_permission', 'RoleController@get_role_permission')->name('get_role_permission'); //Get Roles list
Route::post('update_roles', 'RoleController@update_roles')->name('update_roles'); //Update Roles Permission

Route::get('profile', 'RoleController@roles')->middleware('auth'); //Roles without vue.
//Route::get('roles','RoleController@roles'); //Roles without vue.

/***************************************Scripts Starts Here.. Which will use to set data **************************************************************/
//migrate user from tbluser to users table
Route::get('/migrate-users', 'UserController@migrate')->name('migrate');
Route::get('/set-image-path', 'UserController@SetImagePath')->name('SetImagePath');


Route::get('dashboard-states', 'IncidentController@dashbaord_States')->name('dashboard-states');


/*****************************************  Scripts End Here  ***************************************************************/



Route::get('/{vue_capture?}', function () {
    return view('welcome');
})->where('vue_capture', '[\/\w\.-]*');






//Route::get('{any}', function () {
//    return view('welcome');
//})->where('any', '.*');