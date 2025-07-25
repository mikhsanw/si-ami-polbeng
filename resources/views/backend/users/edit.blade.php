<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="card card-flush py-4">
            <div class="card-body col-8">
                    {!! html()->modelForm($data,'PUT', route($page->code.'.update', $data->id))->id('form-create-'.$page->code)->acceptsFiles()->class('form form-horizontal')->open() !!}

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        {!! html()->label('Nama','name')->class('control-label') !!}
                        <span class="text-danger">*</span>
                        {!! html()->text('name',$data->name)->placeholder('Type name here')->class('form-control')->id('name') !!}
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        {!! html()->label('Email','email')->class('control-label') !!}
                        <span class="text-danger">*</span>
                        {!! html()->text('email',$data->email)->placeholder('Type name here')->class('form-control')->id('email') !!}
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        {!! html()->label('Password','password')->class('control-label') !!}
                        <span class="text-dark">(optional)</span>
                        {!! html()->password('password')->placeholder('Ketik Password')->class('form-control')->id('password') !!}
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        {!! html()->label('Konfirmasi Password','password_confirmation')->class('control-label') !!}
                        {!! html()->password('password_confirmation')->placeholder('Ketik Ulang Password')->class('form-control')->id('password_confirmation') !!}
                    </div>

                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <label for="roles" class="required form-label">Roles</label>
                            <select class="form-select @error('roles') is-invalid @enderror" multiple aria-label="Roles" id="roles" name="roles[]">
                                @forelse ($roles as $role)

                                    @if ($role!='Super Admin')
                                    <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                    @else
                                        @if (Auth::user()->hasRole('Super Admin'))   
                                        <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                        @endif
                                    @endif

                                @empty

                                @endforelse
                            </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <span class="message"></span>
                            <div class="progress" style="display: none;">
                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div id="statustxt">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10 fv-row fv-plugins-icon-container">
                        <button type="submit" id="kt_ecommerce_add_category_submit" class="submit-data btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                        </button>
                    </div>
                    {!! html()->hidden('redirect',route($page->code.'.index'))->id('redirect') !!}
                    {!! html()->form()->close() !!}
            </div>
        </div>
    </div>
</div>    

</x-app-layout>
@push('js')
    <script src="{{ url('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
    <script src="{{ url('js/jquery-crud.js') }}"></script>

@endpush