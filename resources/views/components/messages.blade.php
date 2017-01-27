<!-- will be used to show any messages -->
<div class="flash-message">
	@foreach (['danger', 'warning', 'success', 'info'] as $msg)
		@if(Session::has('alert-' . $msg))
			<p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
		@endif
	@endforeach
</div> <!-- end .flash-message -->

<!-- if there are creation errors, they will show here -->
@if (count($errors) > 0)
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif
