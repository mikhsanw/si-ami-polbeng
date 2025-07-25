<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Nama_variable")->class("control-label fw-bold") !!}
				{!! html()->p("nama_variable") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Label_input")->class("control-label fw-bold") !!}
				{!! html()->p("label_input") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Tipe_data")->class("control-label fw-bold") !!}
				{!! html()->p("tipe_data") !!}
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
