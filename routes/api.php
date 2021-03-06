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

Route::get('/getSetting', 'HomeController@getSetting');
Route::get('/getHomeData', 'HomeController@index');
Route::get('/getCategory', 'HomeController@getCategory');
Route::get('/getMorePost/{page}', 'HomeController@getMorePost');
Route::get('/getMorePostInCategory/{category_id}/{page}', 'HomeController@getMorePostInCategory');
Route::get('/getPostInfo/{query}', 'HomeController@getPostInfo');
Route::get('/getCategoryInfo/{query}', 'HomeController@getCategoryInfo');
Route::get('/getTopNew', 'HomeController@getTopNew');
Route::get('/getVideo', 'HomeController@getVideo');
Route::get('/getPressReleases', 'HomeController@getPressReleases');
Route::post('/uploadAvatar', 'HomeController@uploadAvatar');

Route::get('/postThumb/{domain}/{date}/{filename}', function ($domain,$date,$filename) {
	$path = storage_path() . '/app/public/post-thumb/' . $date . '/' . $filename;
	if(!File::exists($path)) return response("File not found",404);
	return response()->file($path);
});