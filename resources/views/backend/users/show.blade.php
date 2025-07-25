<x-app-layout>
    <x-slot name="title">
        {{ __($page->title) }}
    </x-slot>
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="card card-flush py-4">
            <div class="card-body col-8">
            <div class="row">
                <label for="name" class="col-md-4 col-form-label text-start"><strong>Name:</strong></label>
                <div class="col-md-6">
                    {{ $user->name }}
                </div>
            </div>
            <hr>
            <div class="row">
                <label for="email" class="col-md-4 col-form-label text-start"><strong>Email Address:</strong></label>
                <div class="col-md-6">
                    {{ $user->email }}
                </div>
            </div>
            <hr>
            <div class="row">
                <label for="roles" class="col-md-4 col-form-label text-start"><strong>Roles:</strong></label>
                <div class="col-md-6">
                    @forelse ($user->getRoleNames() as $role)
                        <span class="badge bg-primary">{{ $role }}</span>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>