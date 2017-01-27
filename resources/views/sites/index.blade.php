@extends('layouts.app')

@section('content')

<!-- will be used to show any messages -->
<div class="flash-message">
	@foreach (['danger', 'warning', 'success', 'info'] as $msg)
		@if(Session::has('alert-' . $msg))
			<p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
		@endif
	@endforeach
</div> <!-- end .flash-message -->
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">

			<h1 class="card-title">Your sites</h1>

			<table class="table table-striped table-default">
				<thead>
					<tr>
						<td>Title</td>
						<td>Url</td>
						<td>Actions</td>
					</tr>
				</thead>
				<tbody>
				@foreach($pages as $key => $value)
					<tr>
						<td>{{ $value->title }}</td>
						 <td><code>www.kent.ac.uk{{ $value->route->path }}</code></td>
						<!-- we will also add show, edit, and delete buttons -->
						<td>
							<a class="btn btn-small btn-info" href="{{ URL::to('site/' . $value->id . '/') }}">Manage this site</a>
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>

		</div>
	</div>
</div>

@endsection
