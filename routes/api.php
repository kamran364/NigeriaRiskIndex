<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

Route::group(['prefix' => 'auth'], function ($router) {
	Route::post('register', 'AuthController@register');
	Route::post('login', 'AuthController@login')->name('login');
	Route::post('logout', 'AuthController@logout');
	Route::post('refresh', 'AuthController@refresh');
	Route::post('me', 'AuthController@me');

});

// Get Chartable Data
Route::get('get-chartable-data', 'IncidentController@getChartableData')->name('get-chartable-data');
Route::delete('delete-chartable-data/{id}', 'IncidentController@deleteChartableData')->name('delete-chartable-data');
// Get Weekly Incidents Data
Route::get('get-weekly-incidents-data', 'IncidentController@getWeeklyIncidentsData')->name('get-weekly-incidents-data');
Route::delete('delete-weekly-incidents-data/{id}', 'IncidentController@deleteWeeklyIncidentsData')->name('delete-weekly-incidents-data');
// Get Weekly News Data
Route::get('get-weekly-news-data', 'IncidentController@getWeeklyNewsData')->name('get-weekly-news-data');
//User Profile
Route::get('api/profile', 'HomeController@profile')->name('profile');

Route::get('get-users', 'HomeController@user_list')->middleware('auth:api'); //Get Users list
Route::post('add-user', 'UserController@add_user'); //Get Users list
Route::post('validate-email', 'UserController@validate_email'); //Get Users list

Route::post('profile', 'UserController@save_profile')->name('profile'); //Save user Profile
// User Password Mawnagement
Route::post('forget-password' , 'UserController@forgetPassword');
Route::post('reset-password' , 'UserController@resetPassword');
//Get User Data For Edit
Route::get('get-user-data/{id}','UserController@getUserData')->name('getUserData');
Route::post('update-user','UserController@updateUser')->name('getUserData');

Route::get('get-roles', 'RoleController@get_role'); //Get Roles list
Route::get('get_permission', 'RoleController@get_permission'); //Get Roles list
Route::get('get_role_permission', 'RoleController@get_role_permission'); //Get Roles list

//Risk factors List
Route::get('get-risk-factors', 'IncidentController@getRiskFactors')->name('get-risk-factors');
//Get Factor for edit
Route::get('get-factor/{id}','Incidentreset-passwordController@getFactor')->name('get-factor');
Route::post('edit_factor','IncidentController@editFactor')->name('edit-factor');
Route::post('add_factor','IncidentController@addFactor')->name('addFactor');

Route::get('/get-factor-list','IncidentController@factor_list')->name('factor_list');
Route::post('/add_indicator','IncidentController@add_indicator')->name('add_indicator');
//Delete Risk indicator
Route::delete('delete-indicator/{id}', 'IncidentController@deleteIndicator')->name('deleteIndicator');
//Get Each indicator
Route::get('get-indicator/{id}','IncidentController@getIndicator')->name('get-indicator');
//Move Indicators
Route::post('move_indicators','IncidentController@moveIndicators')->name('moveIndicators');
// Filtered Risk Indicators
Route::get('get-filtered-risk-indicators', 'IncidentController@getFilteredRiskIndicators')->name('get-filtered-risk-indicators');
// Get States
Route::get('get-states', 'IncidentController@getStates')->name('get-states');
// Get Lga
Route::get('get-lgas', 'IncidentController@getLgas')->name('get-lgas');
// Search Incidents
Route::get('search-incidents', 'IncidentController@searchIncidents')->name('search-incidents');
// Get Selected Incident Data
Route::get('get-selected-incident', 'IncidentController@getSelectedIncidentData')->name('get-selected-incident');
Route::get('get-incident-by-event', 'IncidentController@getIncidentbyEventId')->name('get-incident-by-event');
//Add Incidents
Route::post('add-incident', 'IncidentController@add_incidents')->name('add_incidents');
// Get Online Newspapers List
Route::get('get-enewspapers', 'IncidentController@getENewspapers')->name('get-enewspapers');
Route::delete('delete-enewspapers/{id}', 'IncidentController@deleteENewspapers')->name('delete-enewspapers');
// Get Countries
Route::get('get-countries', 'IncidentController@getCountryList')->name('get-countries');
// Add new E-Newspapere scrape site
Route::post('add-new-scrape-site', 'IncidentController@addNewScrapeSite')->name('add-new-scrape-site');
// Get Manage Advisay List
Route::get('get-manage-advisory', 'IncidentController@getManageAdvisory')->name('get-manage-advisory');
// Get Event for Update Casualities
Route::get('get-update-casualties-event', 'IncidentController@getUpdateCasualtiesEvent')->name('get-update-casualties-event');
// Udpate Casualities
Route::post('update-casualties', 'IncidentController@updateCasualities')->name('update-casualties');
// Get Factor Sub Categories
Route::get('get-factor-subcategory', 'IncidentController@getFactorSubCat')->name('get-factor-subcategory');
// Update Coordinates Page Record
Route::get('get-update-coordinate-record', 'IncidentController@getUpdateCoordinateRecord')->name('get-update-coordinate-record');
//Get All non cordinates Records
Route::get('get-update-coordinate-list', 'IncidentController@getUpdateCoordinateList')->name('get-update-coordinate-list');
//Get only one incidents Data
Route::get('get-one-incidents-data', 'IncidentController@getOneIncidentData')->name('get-one-incidents-data');
// Get Authors List
Route::get('get-authors-list', 'IncidentController@GetAuthorsList')->name('get-authors-list');
//Route::get('/get-reports','ReportsController@index')->name('get_reports');
Route::get('/get-reports/{year?}','ReportsController@yearly_report')->name('yearly_report');
//Route::get('/get-reports','ReportsController@yearly_report')->name('yearly_report');
//News
Route::post('add_news','NewsController@add_news')->name('add_news');
// Get Review Data Entries Record
Route::get('get-entries-list','IncidentController@getReviewDataEntries')->name('get-entries-list');
// Get Incidents count
Route::get('get-incidents-list','IncidentController@getIncidentsCount')->name('get-incidents-list');
// Get subscribed states
Route::get('get-subscribed-states', 'IncidentController@getSubscribedStates')->name('get-subscribed-states');
// Get News Feeds
Route::get('get-news-feeds', 'IncidentController@getNewsFeed')->name('get-news-feeds');
// Get Incidents Count by year
Route::get('get-all-incidents-year', 'IncidentController@getIncidentsCountByYear')->name('get-all-incidents-year');
// Compare States
Route::get('compare-states', 'IncidentController@fun_compareStates')->name('compare-states');
// Get Visualize Data
Route::get('get-visualize-data', 'IncidentController@fun_getStateVisualizeData')->name('get-visualize-data');
// Get Visualize Data
Route::get('get-state-dashboard', 'IncidentController@GetStateDashboard')->name('get-state-dashboard');
// Get Lga Incidents Data
Route::get('get-lga-incidents-data', 'IncidentController@getLgaFactorsStats')->name('get-lga-incidents-data');
//Get Week Number by date for incidents
Route::get('get-week-number', 'IncidentController@getWeekNumber')->name('get_week_number');

Route::get('dashboard-states', 'IncidentController@dashbaord_States')->name('dashboard-states');
//Get General Dashboard
Route::get('get-dashboard-data', 'IncidentController@get_dashboard_data')->name('get-dashboard-data');

