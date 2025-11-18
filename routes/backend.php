<?php

use Illuminate\Support\Facades\Route;

//file
Route::prefix('file')->as('file')->group(function () {
    Route::get('stream/{id}/{name}', 'FileController@getFile');
    Route::get('download/{id}/{name}', 'FileController@downloadFile');
    Route::get('delete/{id}/{name}', 'FileController@deleteFile');
    Route::post('upload-image-editor', 'FileController@handleEditorImageUpload');
});

Route::group(['middleware' => ['auth', 'check.permission']], function () {
    Route::get('/home', 'DashboardController@index')->name('home');
    Route::post('/sorted', 'MenuController@sorted')->name('menu.sorted');
    //menus
    Route::prefix('menu')->as('menu')->group(function () {
        Route::get('/data', 'MenuController@data');
        Route::get('delete/{id}', 'MenuController@delete');
    });
    Route::resource('menu', 'MenuController');
    //users
    Route::prefix('users')->as('users')->group(function () {
        Route::get('delete/{id}', 'UserController@delete');
    });
    Route::resource('users', 'UserController');
    //roles
    Route::resources(['roles' => RoleController::class]);
    //berita
    Route::prefix('berita')->as('berita')->group(function () {
        Route::get('delete/{id}', 'BeritaController@delete');
    });
    Route::resource('berita', 'BeritaController');

    Route::get('/units/delete/{id}', 'UnitsController@delete')->name('units.delete');
    Route::get('/units/detail/{id}', 'UnitsController@show')->name('units.detail');
    Route::get('/units/{id?}', 'UnitsController@index')->name('units.index')->where('id', '[a-f0-9\-]+');
    Route::resource('units', 'UnitsController')->except(['index']);

    Route::prefix('lembagaakreditasis')->as('lembagaakreditasis')->group(function () {
        Route::get('/delete/{id}', 'LembagaAkreditasisController@delete');
    });
    Route::resource('lembagaakreditasis', 'LembagaAkreditasisController');

    Route::prefix('instrumentemplates')->as('instrumentemplates.')->group(function () {
        Route::get('/delete/{id}', 'InstrumenTemplatesController@delete');
        Route::get('/edit-rancangan/{id}', 'InstrumenTemplatesController@editRancangan');
        Route::put('/update-rancangan/{id}', 'InstrumenTemplatesController@updateRancangan')->name('update-rancangan');
        Route::get('/get-indikator-tree', 'InstrumenTemplatesController@getIndikatorTree')->name('get-indikator-tree');
    });
    Route::resource('instrumentemplates', 'InstrumenTemplatesController');

    Route::prefix('kriterias')->as('kriterias.')->group(function () {
        Route::get('/delete/{id}', 'KriteriasController@delete')->name('delete');
        Route::get('/create-child/{id}', 'KriteriasController@createChild')->name('create-child');
        Route::get('/create-indikator/{id}', 'KriteriasController@createIndikator')->name('create-indikator');
        Route::get('/delete-indikator/{id}', 'KriteriasController@deleteIndikator')->name('delete-indikator');
        Route::delete('/destroy-indikator/{id}', 'KriteriasController@destroyIndikator')->name('destroy-indikator');
        Route::get('/edit-indikator/{id}', 'KriteriasController@editIndikator')->name('edit-indikator');
        Route::post('/store-indikator', 'KriteriasController@storeIndikator')->name('store-indikator');
        Route::put('/update-indikator/{id}', 'KriteriasController@updateIndikator')->name('update-indikator');
        Route::get('/{id}/indikators', 'KriteriasController@indikators')->name('indikators');
        Route::get('/{id?}', 'KriteriasController@index')->name('index')->where('id', '[a-f0-9\-]+');
        Route::post('/cek-formula', 'KriteriasController@cekFormula')->name('cek-formula');
    });
    Route::resource('kriterias', 'KriteriasController')->except(['index', 'show']);

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

    Route::prefix('hasilaudits')->as('hasilaudits.')->group(function () {
        Route::get('/delete/{id}', 'HasilAuditsController@delete');
        Route::get('/audit-kriteria/{id}', 'HasilAuditsController@auditKriteriaIndex')->name('audit-kriteria');
        Route::post('/update-status-indikator', 'HasilAuditsController@updateStatusIndikator')->name('update-status-indikator');
        Route::get('/{id}/edit', 'HasilAuditsController@edit')->name('edit');
        Route::get('/{id}/show', 'HasilAuditsController@show')->name('show');
        Route::delete('/delete-file', 'HasilAuditsController@deleteFile')->name('deleteFile');
    });
    Route::resource('hasilaudits', 'HasilAuditsController')->except(['edit', 'show']);

    Route::prefix('penugasanaudits')->as('penugasanaudits.')->group(function () {
        Route::get('/delete/{id}', 'PenugasanAuditsController@delete');
        Route::get('/audit-kriteria/{id}', 'PenugasanAuditsController@auditKriteriaIndex')->name('audit-kriteria');
        Route::get('/{id}/edit', 'PenugasanAuditsController@edit')->name('edit');
        Route::get('/{id}/show', 'PenugasanAuditsController@show')->name('show');
    });
    Route::resource('penugasanaudits', 'PenugasanAuditsController')->except(['edit', 'show']);

    Route::prefix('prosesaudits')->as('prosesaudits.')->group(function () {
        Route::get('/{id?}', 'ProsesAuditsController@index')->name('index')->where('id', '[a-f0-9\-]+');
        Route::get('/delete/{id}', 'ProsesAuditsController@delete');
        Route::get('/{id}/edit', 'ProsesAuditsController@edit')->name('edit');
        Route::get('/{id}/show', 'ProsesAuditsController@show')->name('show');
    });
    Route::resource('prosesaudits', 'ProsesAuditsController')->except(['edit', 'index', 'show']);

    Route::prefix('logaktivitasaudits')->as('logaktivitasaudits')->group(function () {
        Route::get('/delete/{id}', 'LogAktivitasAuditsController@delete');
    });
    Route::resource('logaktivitasaudits', 'LogAktivitasAuditsController');

    Route::prefix('dataauditinputs')->as('dataauditinputs')->group(function () {
        Route::get('/delete/{id}', 'DataAuditInputsController@delete');
    });
    Route::resource('dataauditinputs', 'DataAuditInputsController');

    Route::prefix('penugasanauditors')->as('penugasanauditors')->group(function () {
        Route::get('/delete/{id}', 'PenugasanAuditorsController@delete');
    });
    Route::resource('penugasanauditors', 'PenugasanAuditorsController');

    Route::prefix('ringkasantemuanaudits')->as('ringkasantemuanaudits.')->group(function () {
        Route::get('/{id?}', 'Laporan\RingkasanTemuanAuditController@index')->name('index')->where('id', '[a-f0-9\-]+');
        Route::get('/generate-form4/{id}', 'Laporan\RingkasanTemuanAuditController@generateForm4')->name('generate-form4');
        Route::get('/{id}/show', 'Laporan\RingkasanTemuanAuditController@show')->name('show');
    });

    //ringkasanunits
    Route::prefix('ringkasanunits')->as('ringkasanunits.')->group(function () {
        Route::get('/', 'Laporan\RingkasanUnitController@index')->name('index');
        Route::get('/{id}/show', 'Laporan\RingkasanUnitController@show')->name('show');
    });
    //gencrud
});
