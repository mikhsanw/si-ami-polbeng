<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view($this->view.'.index', [
            'users' => $this->model::latest('id')->paginate(3)
        ]);
    }

    public function data(Request $request)
    {
        $data = $this->model::all();
        $user = $request->user();
        return datatables()->of($data)
            ->addColumn('action', function ($data) use ($user) {
                $button ='';
                $button .= '<a class="btn btn-sm btn-light-primary" href="'.route('users.show', $data->id).'"><i class="fa fa-eye text-info"></i></button>';
                if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? []) ){
                    if (auth()->user()->hasRole('Super Admin')){
                    $button.='<a class="btn btn-sm btn-light-warning" href="'.route('users.edit', $data->id).'"> <i class="fa fa-edit text-warning"></i> </a> ';
                    }
                }else{
                    if($user->hasPermissionTo('users edit')){
                        $button.='<a class="btn btn-sm btn-light-warning" href="'.route('users.edit', $data->id).'"> <i class="fa fa-edit text-warning"></i> </a> ';
                    }
                    if($user->hasPermissionTo('users delete')){
                        $button.='<button class="btn-delete btn btn-sm btn-light-danger" data-title="Delete" data-action="delete" data-url="'.$this->url.'" data-id="'.$data->id.'" title="Delete"> <i class="fa fa-trash text-danger"></i> </button>';
                    }
                }
                return "<div class='btn-group'>".$button."</div>";
            })
            ->editColumn('role', function (User $user) {
                return ucwords($user->roles->first()?->name);
            })
            ->editColumn('last_login_at', function (User $user) {
                return sprintf('<div class="badge badge-light fw-bold">%s</div>', $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 
                    ($user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->diffForHumans() : 'N/A'));
            })
            ->addIndexColumn()
            ->rawColumns(['action','last_login_at'])
            ->make();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view($this->view.'.create', [
            'roles' => Role::pluck('name')->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        if($user = User::create($input)){
            $user->assignRole($request->roles);
            $response = ['status' => TRUE, 'message' => 'Data berhasil disimpan'];
        }
        return response()->json($response ?? ['status' => FALSE, 'message' => 'Data gagal disimpan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view($this->view.'.show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        // Check Only Super Admin can update his own Profile
        if ($user->hasRole('Super Admin')){
            if($user->id != auth()->user()->id){
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        return view($this->view.'.edit', [
            'data' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $input = $request->all();
 
        if(!empty($request->password)){
            $input['password'] = Hash::make($request->password);
        }else{
            $input = $request->except('password');
        }
        
        if($user->update($input)){
            $user->syncRoles($request->roles);
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil perbaharui'];
        };
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal perbaharui']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $data=$this->model::find($id);
        return view($this->view.'.delete', compact('data'));
    }
    public function destroy(User $user)
    {
        // About if user is Super Admin or User ID belongs to Auth User
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id)
        {
            return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal dihapus']);
        }

        $user->syncRoles([]);
        if($user->delete()){
            $response=[ 'status'=>TRUE, 'message'=>'Data berhasil dihapus'];
        };
        return response()->json($response ?? ['status'=>FALSE, 'message'=>'Data gagal dihapus']);

    }

}
