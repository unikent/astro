@extends('layouts.app')

@section('content')

<div class="container-fluid">

	@include('components.header')

	<div class="row row-offcanvas row-offcanvas-left" style="margin-top:4rem;">

		@include('components.pagelist')

		<div class="col-sm-12 row-oncanvas">

			@include('components.messages')

				<div class="panel panel-default">
					<div id="{{$page->id}}" class="pageid"></div>
					<div class="panel-body">
						<div id="app">
							<page></page>
						</div>
					</div>
				</div>
		</div><!-- /.col -->

	</div><!-- /.row -->
</div>


@endsection
