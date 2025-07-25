<?php
use Illuminate\Support\Facades\Route;

//file
Route::prefix('file')->as('file')->group(function () {
    Route::get('stream/{id}/{name}', "FileController@getFile");
    Route::get('download/{id}/{name}', "FileController@downloadFile");
    Route::get('delete/{id}/{name}', "FileController@deleteFile");
    Route::post('upload-image-editor', 'FileController@handleEditorImageUpload');
});

Route::group(['middleware'=>['role:Admin|Super Admin']], function () {
    Route::get('/home', "DashboardController@index")->name('home');
    Route::post('/sorted', "MenuController@sorted")->name('menu.sorted');
    //menus
    Route::prefix('menu')->as('menu')->group(function () {
        Route::get('/data', "MenuController@data");
        Route::get('delete/{id}', "MenuController@delete");
    });
    Route::resource('menu', "MenuController");
    //users
    Route::prefix('users')->as('users')->group(function () {
        Route::get('/data', "UserController@data");
        Route::get('delete/{id}', "UserController@delete");
    });
    Route::resource('users', "UserController");
    //roles
    Route::resources(['roles' => RoleController::class]);
    //berita
    Route::prefix('berita')->as('berita')->group(function () {
        Route::get('delete/{id}', 'BeritaController@delete');
    });
    Route::resource('berita', 'BeritaController');

	Route::get('/units/delete/{id}', 'UnitsController@delete')->name('units.delete');
	Route::get('/units/detail/{id}', 'UnitsController@show')->name('units.detail');
	Route::get('/units/{id?}', 'UnitsController@index')->name('units.index')->where('id', '[a-f0-9\-]+');;
	Route::resource('units', 'UnitsController')->except(['index']);

	Route::prefix('lembagaakreditasis')->as('lembagaakreditasis')->group(function () {
		Route::get('/delete/{id}', 'LembagaAkreditasisController@delete');
	});
	Route::resource('lembagaakreditasis', 'LembagaAkreditasisController');

	Route::prefix('instrumentemplates')->as('instrumentemplates')->group(function () {
		Route::get('/delete/{id}', 'InstrumenTemplatesController@delete');
	});
	Route::resource('instrumentemplates', 'InstrumenTemplatesController');

	Route::prefix('kriterias')->as('kriterias')->group(function () {
		Route::get('/delete/{id}', 'KriteriasController@delete');
	});
	Route::resource('kriterias', 'KriteriasController');

	Route::prefix('templatekriterias')->as('templatekriterias')->group(function () {
		Route::get('/delete/{id}', 'TemplateKriteriasController@delete');
	});
	Route::resource('templatekriterias', 'TemplateKriteriasController');

	Route::prefix('indikators')->as('indikators')->group(function () {
		Route::get('/delete/{id}', 'IndikatorsController@delete');
	});
	Route::resource('indikators', 'IndikatorsController');

	Route::prefix('rubrikpenilaians')->as('rubrikpenilaians')->group(function () {
		Route::get('/delete/{id}', 'RubrikPenilaiansController@delete');
	});
	Route::resource('rubrikpenilaians', 'RubrikPenilaiansController');

	Route::prefix('rubrikpenilaians')->as('rubrikpenilaians')->group(function () {
		Route::get('/delete/{id}', 'RubrikPenilaiansController@delete');
	});
	Route::resource('rubrikpenilaians', 'RubrikPenilaiansController');

	Route::prefix('auditperiodes')->as('auditperiodes')->group(function () {
		Route::get('/delete/{id}', 'AuditPeriodesController@delete');
	});
	Route::resource('auditperiodes', 'AuditPeriodesController');

	Route::prefix('rubrikpenilaians')->as('rubrikpenilaians')->group(function () {
		Route::get('/delete/{id}', 'RubrikPenilaiansController@delete');
	});
	Route::resource('rubrikpenilaians', 'RubrikPenilaiansController');

	Route::prefix('indikatorinputs')->as('indikatorinputs')->group(function () {
		Route::get('/delete/{id}', 'IndikatorInputsController@delete');
	});
	Route::resource('indikatorinputs', 'IndikatorInputsController');

	Route::prefix('indikatorinputs')->as('indikatorinputs')->group(function () {
		Route::get('/delete/{id}', 'IndikatorInputsController@delete');
	});
	Route::resource('indikatorinputs', 'IndikatorInputsController');

	Route::prefix('hasilaudits')->as('hasilaudits')->group(function () {
		Route::get('/delete/{id}', 'HasilAuditsController@delete');
	});
	Route::resource('hasilaudits', 'HasilAuditsController');

	Route::prefix('logaktivitasaudits')->as('logaktivitasaudits')->group(function () {
		Route::get('/delete/{id}', 'LogAktivitasAuditsController@delete');
	});
	Route::resource('logaktivitasaudits', 'LogAktivitasAuditsController');

	Route::prefix('dataauditinputs')->as('dataauditinputs')->group(function () {
		Route::get('/delete/{id}', 'DataAuditInputsController@delete');
	});
	Route::resource('dataauditinputs', 'DataAuditInputsController');

//gencrud
});