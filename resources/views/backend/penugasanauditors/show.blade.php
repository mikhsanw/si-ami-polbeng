<div class="panel">
    <div class="panel-body">
        <div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("user_id")->class("control-label fw-bold") !!}
				{!! html()->p("user_id") !!}
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				{!! html()->span()->text("Audit_periode_id")->class("control-label fw-bold") !!}
				{!! html()->p("audit_periode_id") !!}
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
