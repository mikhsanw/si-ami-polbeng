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
				{!! html()->span()->text("Singkatan")->class("control-label fw-bold") !!}
				{!! html()->p("singkatan") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Kategori")->class("control-label fw-bold") !!}
				{!! html()->p("kategori") !!}
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
