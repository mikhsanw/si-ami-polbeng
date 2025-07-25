<x-app-layout>

<div class="mb-3 row">
    <label for="name" class="col-md-4 col-form-label text-md-end text-start"><strong>Name:</strong></label>
    <div class="col-md-6" style="line-height: 35px;">
        {{ $role->name }}
    </div>
</div>

<div class="mb-3 row">
    <label for="roles" class="col-md-4 col-form-label text-md-end text-start"><strong>Permissions:</strong></label>
    <div class="col-md-6" style="line-height: 35px;">
        @if ($role->name=='Super Admin')
            <span class="badge bg-primary">All</span>
        @else
            @forelse ($rolePermissions as $permission)
                <span class="badge bg-primary">{{ $permission->name }}</span>
            @empty
            @endforelse
        @endif
    </div>
</div>

</x-app-layout>