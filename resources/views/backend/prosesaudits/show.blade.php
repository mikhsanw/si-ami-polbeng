<div class="panel">
    <div class="panel-body">
        <div class="mb-4 p-3 bg-body-tertiary rounded border">
			<h5 class="fw-bold border-bottom pb-2 mb-3">Informasi Umum Indikator</h5>

			{{-- Menampilkan Nama Indikator --}}
			<div class="row mb-2">
				<div class="col-md-4 text-muted">Nama Indikator</div>
				<div class="col-md-8 fw-medium">{{ $data->indikator->nama ?? 'Tidak tersedia' }}</div>
			</div>

			{{-- Menampilkan Tipe Penilaian --}}
			<div class="row mb-2">
				<div class="col-md-4 text-muted">Tipe Penilaian</div>
				<div class="col-md-8">
					@if($data->indikator->tipe === 'LED')
						<span class="badge bg-primary">{{ $data->indikator->tipe }}</span>
					@else
						<span class="badge bg-success">{{ $data->indikator->tipe }}</span>
					@endif
				</div>
			</div>

			{{-- Menampilkan Kriteria Induk --}}
			<div class="row">
				<div class="col-md-4 text-muted">Kriteria Induk</div>
				<div class="col-md-8 fw-medium">
					{{-- Lebih baik menampilkan nama kriteria daripada hanya ID-nya --}}
					{{ $data->indikator->kriteria->nama ?? 'Tidak terhubung' }}
				</div>
			</div>
		</div>

		{{-- =============================================== --}}
		{{-- BAGIAN BARU: MENAMPILKAN HASIL AUDIT --}}
		{{-- =============================================== --}}
		<div class="mb-4 p-3 rounded border">
			<h5 class="fw-bold border-bottom pb-2 mb-3">Hasil Audit</h5>

			@if($data)
				{{-- Menampilkan Status Terkini --}}
				<div class="row mb-3">
					<div class="col-md-4 text-muted">Status</div>
					<div class="col-md-8">
						@if ($data->status_terkini === 'Selesai')
							<span class="badge badge-light-success">Selesai</span>
						@elseif ($data->status_terkini === 'Revisi')
							<span class="badge badge-light-danger">Revisi Diperlukan</span>
						@elseif ($data->status_terkini === 'Diajukan')
							<span class="badge badge-light-warning">Menunggu Validasi</span>
						@else
							<span class="badge badge-light">Belum Dikerjakan</span>
						@endif
					</div>
				</div>

				{{-- Menampilkan Skor Auditee --}}
				<div class="row mb-3">
					<div class="col-md-4 text-muted">Skor Auditee</div>
					<div class="col-md-8 fw-bold fs-5 text-primary">{{ $data->skor_auditee ?? '-' }}</div>
				</div>

				{{-- Menampilkan Skor Final Auditor --}}
				<div class="row mb-3">
					<div class="col-md-4 text-muted">Skor Final Auditor</div>
					<div class="col-md-8 fw-bold fs-5 text-success">{{ $data->skor_final ?? 'Belum divalidasi' }}</div>
				</div>

				{{-- Menampilkan Catatan Final Auditor --}}
				<div class="row mb-3">
					<div class="col-md-4 text-muted">Catatan Final Auditor</div>
					<div class="col-md-8">
						<p class="fst-italic mb-0">"{{ $data->catatan_final ?? 'Tidak ada catatan.' }}"</p>
					</div>
				</div>

				{{-- Menampilkan Bukti Terlampir --}}
				<div class="row">
					<div class="col-md-4 text-muted">Bukti Terlampir</div>
					<div class="col-md-8">
						@if($data->files->isNotEmpty())
							<div class="list-group list-group-flush">
								@foreach ($data->files as $key => $file)
									<a href="{{ asset($file->link_public_stream)}}" class="list-group-item list-group-item-action px-0 py-1">
										<i class="fas fa-file-alt fa-fw me-2 text-primary"></i> {{ 'File Bukti ' . ($key + 1) }}
									</a>
								@endforeach
							</div>
						@else
							<p class="mb-0 text-muted">Tidak ada bukti yang dilampirkan.</p>
						@endif
					</div>
				</div>

			@else
				<div class="alert alert-secondary text-center">
					Indikator ini belum dikerjakan untuk periode audit ini.
				</div>
			@endif
		</div>

    </div>
</div>
<style>
    .modal-lg {
        max-width: 1000px !important;
    }
</style>
<script>
    $('.submit-data').hide();
    $('.modal-title').html('<i class="fa fa-search"></i> Detail Data {!! $page->title !!}');
</script>
