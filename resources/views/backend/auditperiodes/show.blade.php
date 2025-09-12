<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Tahun_akademik")->class("control-label fw-bold") !!}
				{!! html()->p("tahun_akademik") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Status")->class("control-label fw-bold") !!}
				{!! html()->p("status") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Unit_id")->class("control-label fw-bold") !!}
				{!! html()->p("unit_id") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("instrumen_template_id")->class("control-label fw-bold") !!}
				{!! html()->p("instrumen_template_id") !!}
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
