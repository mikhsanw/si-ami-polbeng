<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Nama")->class("control-label fw-bold") !!}
				{!! html()->p("nama") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Deskripsi")->class("control-label fw-bold") !!}
				{!! html()->p("deskripsi") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Is_active")->class("control-label fw-bold") !!}
				{!! html()->p("is_active") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Lembaga_akreditasi_id")->class("control-label fw-bold") !!}
				{!! html()->p("lembaga_akreditasi_id") !!}
			</div>
		</div>

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
