<x-app-layout>
    <x-slot name="title">
        {{ __($page->title).' - '.$auditPeriode->tahun_akademik }}
    </x-slot>
    
<div class="card">
    <div class="card-header border-0 pt-6">
        <h1 class="text-muted">{{ $page->title }}</h1>
        <div class="card-toolbar">
        <div class="d-flex justify-content-end mb-3">
            <label class="form-check form-switch form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" id="toggle-all-kriteria">
                <span class="form-check-label">
                    Tampilkan Semua
                </span>
            </label>
        </div>
    </div>
    </div>
    
    <div class="card-body py-4">
        <div class="m-0">
            @foreach ($kriterias as $kriteria)
                @include($backend.'.hasilaudits._kriteria_item', ['item' => $kriteria, 'parentId' => 'root'])
                <div class="separator separator-dashed"></div>
            @endforeach
        </div>

    </div>
</div>

@prepend('css')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endprepend
@prepend('js')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('js/'.$backend.'/'.$page->code.'/datatables.js') }}"></script>
<script src="{{ asset('js/jquery-validation-1.19.5/lib/jquery.form.js') }}"></script>
<script src="{{ asset('js/jquery-crud.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleSwitch = document.getElementById('toggle-all-kriteria');
        if (!toggleSwitch) return;
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap JS not found. Toggle-all-kriteria requires Bootstrap collapse JS.');
            return;
        }

        // Semua elemen collapse kriteria
        const collapseEls = Array.from(document.querySelectorAll('.collapse'));

        // Map tiap collapse -> trigger(s) yang membuka/menutupnya
        const collapseToTriggers = new Map();
        collapseEls.forEach(el => {
            const idSelector = `#${el.id}`;
            const triggers = Array.from(document.querySelectorAll(`[data-bs-target="${idSelector}"], [href="${idSelector}"]`));
            collapseToTriggers.set(el, triggers);
        });

        // cek apakah semua collapse saat ini dalam keadaan "show"
        function areAllShown() {
            return collapseEls.length > 0 && collapseEls.every(el => el.classList.contains('show'));
        }

        // update state switch sesuai keadaan collapse
        function updateSwitchState() {
            toggleSwitch.checked = areAllShown();
        }

        // sinkronkan kelas 'collapsed' pada trigger terkait sebuah collapse
        function updateTriggersFor(el) {
            const triggers = collapseToTriggers.get(el) || [];
            const shown = el.classList.contains('show');
            triggers.forEach(t => {
                if (shown) t.classList.remove('collapsed');
                else t.classList.add('collapsed');
            });
        }

        // attach event listener untuk mendengar perubahan manual (user click)
        collapseEls.forEach(el => {
            el.addEventListener('shown.bs.collapse', function () {
                updateTriggersFor(el);
                updateSwitchState();
            });
            el.addEventListener('hidden.bs.collapse', function () {
                updateTriggersFor(el);
                updateSwitchState();
            });
        });

        // inisialisasi awal (jika beberapa sudah terbuka)
        updateSwitchState();

        // ketika user toggle switch -> buka/tutup semua
        toggleSwitch.addEventListener('change', function () {
            const open = this.checked;
            toggleSwitch.disabled = true; // sementara blok input untuk mencegah spam
            collapseEls.forEach(el => {
                const inst = bootstrap.Collapse.getOrCreateInstance(el);
                if (open) inst.show();
                else inst.hide();

                // update trigger langsung (agar UI tidak delay terlalu lama)
                const triggers = collapseToTriggers.get(el) || [];
                triggers.forEach(t => {
                    if (open) t.classList.remove('collapsed');
                    else t.classList.add('collapsed');
                });
            });

            // re-enable setelah transisi (sesuaikan timeout jika transisi lebih panjang)
            setTimeout(() => {
                toggleSwitch.disabled = false;
                updateSwitchState();
            }, 400);
        });
    });
</script>
@endprepend

</x-app-layout>