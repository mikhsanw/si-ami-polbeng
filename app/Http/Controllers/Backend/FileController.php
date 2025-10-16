<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;

class FileController extends Controller
{
    public function getFile($id, $filename)
    {
        if ($file = File::find($id)) {
            if ($file->exists) {
                return response()->make($file->take, 200, [
                    'Content-Type' => $file->mime, 'Content-Disposition' => 'inline; filename="'.$filename.'.'.$file->extension.'"',
                ]);
            }
        }

        return view('errors.404', [
            'data' => [
                'code' => 410, 'status' => 'GONE', 'title' => 'File Not Found', 'message' => 'im sorry, the file you are looking for is not found',
            ],
        ]);
    }

    public function downloadFile($id, $filename)
    {
        if ($file = File::find($id)) {
            if ($file->exists) {
                return response()->make($file->take, 200, [
                    'Content-Type' => $file->mime, 'Content-Disposition' => 'attachment; filename="'.$filename.'.'.$file->extension.'"',
                ]);
            }
        }

        return view('errors.404', [
            'data' => [
                'code' => 410, 'status' => 'GONE', 'title' => 'File Not Found', 'message' => 'im sorry, the file you are looking for is not found',
            ],
        ]);
    }

    public function deleteFile($id, $filename)
    {
        if ($file = File::find($id)) {
            if ($file->exists()) {
                $file->delete();

                return response()->json(['status' => true, 'message' => "File $filename has been deleted"]);
            }
        }
        throw new ModelNotFoundException("File $filename not found", 404);
    }

    // public function publicFileStream(Request $request, $code_menu)
    // {
    //     $code = $request->code;
    //     try {
    //         $menu = $this->help->menu($code_menu);
    //         $data = $menu->model::find($request->id) ?? $menu->model::first();
    //         $code = explode('?', $code)[0];
    //         $file = $data->file()->whereAlias($code)->first();
    //         $path = public_path(config('master.app.web.template').'/'.$file->target);
    //         if ($file->exists()) {
    //             return response()->make($file->take, 200, ['Content-Type' => $file->mime, 'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"']);
    //         } elseif (FacadesFile::exists($path)) {
    //             return response()->file($path);
    //         } else {
    //             return file_get_contents($file->target);
    //         }
    //     } catch (\Throwable $th) {
    //         return view('errors.404', ['data' => ['code' => 404, 'status' => 'GONE', 'file' => $code, 'title' => 'File '.$code.' Not Found', 'message' => 'im sorry, the file you are looking for is not found']]);
    //     }
    // }

    public function publicFileStream(Request $request, $code_menu)
    {
        try {
            // Ambil ID file dari query
            $fileId = $request->id;

            // Cari file langsung berdasarkan ID
            $file = \App\Models\File::findOrFail($fileId);

            // Tentukan path absolut file di storage
            $path = public_path(config('master.app.web.template').'/'.$file->target);

            // CASE 1 — File punya konten langsung (misal disimpan di DB)
            if ($file->exists()) {
                return response()->make(
                    $file->take,
                    200,
                    [
                        'Content-Type' => $file->mime,
                        'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"',
                    ]
                );
            }

            // CASE 2 — File fisik ada di storage
            if (FacadesFile::exists($path)) {
                return response()->file($path, [
                    'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"',
                ]);
            }

            // CASE 3 — Fallback ke target path manual
            if ($file->target && is_readable($file->target)) {
                return response()->file($file->target, [
                    'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"',
                ]);
            }

            // CASE 4 — File tidak ditemukan
            return response()->view('errors.404', [
                'data' => [
                    'code' => 404,
                    'status' => 'GONE',
                    'file' => $fileId,
                    'title' => 'File Not Found',
                    'message' => 'Sorry, the file you are looking for could not be found.',
                ],
            ], 404);

        } catch (\Throwable $th) {
            // Tangani semua error dengan view 404 custom
            return response()->view('errors.404', [
                'data' => [
                    'code' => 404,
                    'status' => 'GONE',
                    'file' => $request->id,
                    'title' => 'File Not Found',
                    'message' => 'Sorry, the file you are looking for could not be found.',
                ],
            ], 404);
        }
    }

    // public function publicFileThumbnailStream(Request $request, $code_menu)
    // {
    //     $code = $request->code;
    //     try {
    //         $menu = $this->help->menu($code_menu);
    //         $data = $menu->model::find($request->id) ?? $menu->model::first();
    //         $code = explode('?', $code)[0];
    //         $file = $data->file()->whereAlias($code)->first();

    //         return response()->make($file->take_thumbnail, 200, ['Content-Type' => $file->mime, 'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"']);
    //     } catch (\Throwable $th) {
    //         return view('errors.404', ['data' => ['code' => 404, 'status' => 'GONE', 'file' => $code, 'title' => 'File '.$code.' Not Found', 'message' => 'im sorry, the file you are looking for is not found']]);
    //     }
    // }

    public function publicFileThumbnailStream(Request $request, $code_menu)
    {
        $code = $request->code;
        try {
            $menu = $this->help->menu($code_menu);
            $data = $menu->model::find($request->id) ?? $menu->model::first();
            $code = explode('?', $code)[0];
            $file = $data->file()->whereAlias($code)->first();

            return response()->make($file->take_thumbnail, 200, ['Content-Type' => $file->mime, 'Content-Disposition' => 'inline; filename="'.$file->name.'.'.$file->extension.'"']);
        } catch (\Throwable $th) {
            return view('errors.404', [
                'data' => [
                    'code' => 404, 'status' => 'GONE', 'file' => $code, 'title' => 'File '.$code.' Not Found', 'message' => 'im sorry, the file you are looking for is not found',
                ],
            ]);
        }
    }

    public function handleEditorImageUpload(Request $request)
    {
        $file = $request->file('file');
        $name = uniqid().'_'.$file->getClientOriginalName();
        $mime = $file->getMimeType();
        $image = base64_encode(file_get_contents($file));

        return response()->json([
            'status' => true, 'message' => 'Gambar berhasil diupload', 'data' => [
                'name' => $name, 'url' => 'data:'.$mime.';base64,'.$image,
            ],
        ]);
    }
}
