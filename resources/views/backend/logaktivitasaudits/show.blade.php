<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Tipe_aksi")->class("control-label fw-bold") !!}
				{!! html()->p("tipe_aksi") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Catatan_aksi")->class("control-label fw-bold") !!}
				{!! html()->p("catatan_aksi") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Hasil_audit_id")->class("control-label fw-bold") !!}
				{!! html()->p("hasil_audit_id") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("User_id")->class("control-label fw-bold") !!}
				{!! html()->p("user_id") !!}
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
