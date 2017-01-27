<div class="col-sm-4 sidebar-offcanvas">
	<div id="js-page-list"></div>
	<a class="btn btn-primary" href="{{ url('/') }}/site/{{ $site->id }}/page/create">Make a new page</a>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Confirm delete</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				Are you sure you want to perform this action?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary">Delete</button>
			</div>
		</div>
	</div>
</div>
<script>
window.pageListData = {
	'site_id': {{ $site->id }}
}
</script>