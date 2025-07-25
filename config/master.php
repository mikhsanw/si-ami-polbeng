<?php
/**
 * Main Master Configuration
 *
 * @package Main Master Configuration
 * @version 1.0.0
 * @license MIT
 */
return [
    'app'=>[
        'profile'=>[
            'name'=>'New Master',
            'short_name'=>'MM',
            'description'=>'New Master is a automatic CRUD generator for Laravel 11',
            'keywords'=>'New Master, Laravel, CRUD',
            'author'=>'@mikhsanw', // Your name or company
            'version'=>'1.0.0', // major.minor.patch
            'laravel'=>'10.0', // Laravel version
        ],
        'root'=>[
            'backend'=>'App/Http/Controllers/Backend', // path to backend controller
            'frontend'=>'App/Http/Controllers/Frontend', // path to frontend controller
            'model'=>'App/Models', // path to model
            'view'=>'views/backend' // path to backend view
        ],
        'url'=>[
            'backend'=>'admin', // url for backend
            'frontend'=>'web', // url for frontend
        ],
        'view'=>[
            'backend'=>'backend', // path to backend view
            'frontend'=>'frontend', // path to frontend view
        ],
        'web'=>[
            'template'=>'eduadmin', // template for frontend view (default: eduadmin)
            'icon'=>'',
            'logo_light'=>'/images/logo-main-master.png',
            'logo_dark'=>'/images/logo-main-master.png',
            'favicon'=>'/images/favicon.ico',
            'background'=>'/images/auth-bg/bg-1.jpg',
            'header_animation'=>'on', // turn on/off header animation
        ],
        'level'=>[
            'list', 'create', 'edit', 'delete' // level of access for user role and permission module
        ]
    ],
    'content'=>[
        'announcement'=>[
            'status'=>[
                'sangat_penting'=>'Sangat Penting',
                'penting'=>'Penting',
                'biasa'=>'Biasa',
            ],
            'color'=>[
                'sangat_penting'=>'danger',
                'penting'=>'warning',
                'biasa'=>'info',
            ],
        ],
        'user'=>[
            'status'=>[
                '1'=>'Aktif',
                '0'=>'Tidak Aktif',
                '2'=>'Keluar',
            ],
            'color'=>[
                '1'=>'success',
                '0'=>'danger',
                '2'=>'warning',
            ],
        ],
        'zona_waktu' => [
            'UTC+7' => 'WIB',
            'UTC+8' => 'WITA',
            'UTC+9' => 'WIT',
        ],
        'unit'=>[
            'tipe'=>[
                'Unit'=>'Unit',
                'Jurusan'=>'Jurusan',
                'Program Studi'=>'Program Studi',
            ],
        ],
        'lembagaakreditasi'=>[
            'kategori'=>[
                'Nasional'=>'Nasional',
                'Internasional'=>'Internasional',
                'Internal'=>'Internal',
            ],
        ],
        'audit_periode'=>[
            'status'=>[
                'persiapan'=>'Persiapan',
                'berlangsung'=>'Berlangsung',
                'selesai'=>'Selesai',
                ],
        ],
    ],
];
