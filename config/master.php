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
            'name'=>'Audit Mutu Internal', // Application name
            'short_name'=>'MM',
            'description'=>'Audit Mutu Internal',
            'keywords'=>'AMI, Laravel, CRUD',
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
        ],
        'validation'=>[
            'regex'=>[
                'json'=>'regex:/^\[.*\]$/', // regex for json validation
                'number'=>'numeric', // regex for number validation
                'string'=>'string', // regex for string validation
                'email'=>'email', // regex for email validation
                'url'=>'url', // regex for url validation
                'date'=>'date', // regex for date validation
                'time'=>'date_format:H:i', // regex for time validation
                'datetime'=>'date_format:Y-m-d H:i:s', // regex for datetime validation
                'boolean'=>'boolean', // regex for boolean validation
                'file'=>'file|mimes:pdf,doc,docx,xls,xlsx|max:2048', // regex for file validation
                'image'=>'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // regex for image validation
                'array'=>'array', // regex for array validation
                'integer'=>'integer', // regex for integer validation
                'uuid'=>'uuid', // regex for uuid validation
            ],
        ],
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
        'kriteria'=>[
            'tipe'=>[
                'LED'=>'LED',
                'LKPS'=>'LKPS'
            ],
        ],
        'hasil_audit'=>[
            'status_terkini'=>[
                'Diajukan'=>'Diajukan',
                'Revisi'=>'Revisi',
                'Selesai'=>'Selesai'
            ],
        ],
        'log_aktivitas_audit'=>[
            'tipe_aksi'=>[
                'SUBMIT_AWAL' => 'SUBMIT AWAL',
                'MINTA_REVISI' => 'MINTA REVISI',
                'SUBMIT_REVISI' => 'SUBMIT REVISI',
                'VALIDASI' => 'VALIDASI',
                'FINALISASI_SKOR' => 'FINALISASI SKOR',

            ],
        ],
    ],
];
