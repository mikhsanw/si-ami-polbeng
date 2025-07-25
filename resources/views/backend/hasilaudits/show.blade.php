<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Skor_auditee")->class("control-label fw-bold") !!}
				{!! html()->p("skor_auditee") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Skor_final")->class("control-label fw-bold") !!}
				{!! html()->p("skor_final") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Catatan_final")->class("control-label fw-bold") !!}
				{!! html()->p("catatan_final") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Status_terkini")->class("control-label fw-bold") !!}
				{!! html()->p("status_terkini") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Audit_periode_id")->class("control-label fw-bold") !!}
				{!! html()->p("audit_periode_id") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Indikator_id")->class("control-label fw-bold") !!}
				{!! html()->p("indikator_id") !!}
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
