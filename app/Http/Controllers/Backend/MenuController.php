<?php

namespace App\Http\Controllers\Backend;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class MenuController extends Controller
{
    public function index() : View
    {
        return view($this->view.'.index');
    }

    public function data()
    {
        $menu=$this->model::with(['children'])->whereNull('parent_id')->sort()->get();
        return view($this->view.'.list-menu.list-menu', compact('menu'));
    }

    public function create()
    {
        $data=[
            'model'=>$this->help::listFile(app_path('/Models'), ['php']),
            'role'=>Role::whereNot('name','Super Admin')->pluck('name', 'id'),
        ];
        return view($this->view.'.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable',
            'title' => 'required|unique:menus',
            'subtitle' => 'nullable',
            'code' => 'required|unique:menus',
            'url' => 'required|unique:menus',
            'model' => 'nullable',
            'icon' => 'nullable',
            'type' => 'required',
            'show' => 'nullable',
            'active' => 'nullable',
            'role_id' => 'required|array|exists:roles,id',
        ]);

        if ($data = $this->model::create($request->all())) {
            foreach ($request->role_id as $role_id) {
                // foreach($request->input('access_crud_'.$role_id) as $key => $permission){
                    foreach(config('master.app.level') as $permissionName){
                            $role = Role::find($role_id);
                            $permission = Permission::firstOrCreate(['name' => $request->code.' '.$permissionName]);
                            if(array_search($permissionName,$request->input('access_crud_'.$role_id))!=''){
                                $role->givePermissionTo($permission);
                            }else{
                                $role->revokePermissionTo($request->code.' '.$permissionName);
                            }
                }
                // $permissions = Permission::whereIn('name', $permissionsname)->get('name')->toArray();
                // $role->syncPermissions($permissions);
            }

            $response = ['status' => TRUE, 'message' => 'Data berhasil disimpan'];
        }
        return response()->json($response ?? ['status' => FALSE, 'message' => 'Data gagal disimpan']);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $data= $this->model::find($id);
        $result=[
            'model'=>$this->help::listFile(app_path('/Models'), ['php']),
            'data'=>$data,
            'role'=>Role::whereNot('name','Super Admin')->pluck('name', 'id'),
            // 'access'=>Role::whereHas()->permissions->pluck('name'),
        ];
        // dd($result);
        return view($this->view.'.edit', $result);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'nullable',
            'title' => 'required',
            'subtitle' => 'nullable',
            'code' => 'required|unique:menus,code,' . $id,
            'url' => 'required|unique:menus,url,' . $id,
            'model' => 'nullable',
            'icon' => 'nullable',
            'type' => 'required',
            'show' => 'nullable',
            'active' => 'nullable',
            'role_id' => 'required|array|exists:roles,id',
        ]);
        if ($data = $this->model::find($id)) {
            if($data->update($request->all())) {
                foreach ($request->role_id as $role_id) {
                    // foreach($request->input('access_crud_'.$role_id) as $key => $permission){
                        // Permission::where('name','LIKE', $request->code.'%')->delete();
                        foreach(config('master.app.level') as $permissionName){
                            $role = Role::find($role_id);
                            $permission = Permission::firstOrCreate(['name' => $request->code.' '.$permissionName]);
                            if(array_search($permissionName,$request->input('access_crud_'.$role_id))!=''){
                                $role->givePermissionTo($permission);
                            }else{
                                $role->revokePermissionTo($request->code.' '.$permissionName);
                            }
                    }
                    // $permissions = Permission::whereIn('name', $permissionsname)->get('name')->toArray();
                    // $role->syncPermissions($permissions);
                }
                $response = ['status' => TRUE, 'message' => 'Data berhasil disimpan'];
                
            }
        }
        return response()->json($response ?? ['status' => FALSE, 'message' => 'Data gagal disimpan']);
    }

    public function delete($id)
    {
        $data=$this->model::find($id);
        return view($this->view.'.delete', compact('data'));
    }

    public function destroy($id)
    {
        $data=$this->model::find($id);
        Permission::where('name','LIKE', $data->code.'%')->delete();
        if ($data->forceDelete()) {
            return response()->json(['status'=>TRUE, 'message'=>'Data berhasil dihapus']);
        }
        return response()->json(['status'=>FALSE, 'message'=>'Data gagal dihapus']);
    }

    public function sorted(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->loopUpdateMenu(json_decode($request->input('sort')));
        }
        return response()->json(['status'=>TRUE, 'message'=>'Menu berhasil diurutkan']);
    }

    function loopUpdateMenu($menu, $parentMenu=NULL)
    {
        if ($menu) {
            foreach ($menu as $key=>$dt) {
                if ($this->model::find($dt->id)->update(['parent_id'=>$parentMenu, 'sort'=>$key + 1])) {
                    if (isset($dt->children) && count($dt->children) > 0) {
                        $this->loopUpdateMenu($dt->children, $dt->id);
                    }
                }
            }
        }
    }

    public function listMenu(Request $request)
    {
        $menu=$this->model::with(['accessChildren'])->whereHas('access_menu', function ($query) use ($request) {
            $query->where('access_group_id', $request->user()->access_group_id);
        })->whereNull('parent_id')->show()->sort()->get();
        return response()->json(['menu'=>$menu])->header('Content-Type', 'application/json');
    }
}
