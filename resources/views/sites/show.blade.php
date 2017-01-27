@extends('layouts.app')

@section('content')

<div class="container-fluid">

	@include('components.header')

	<div class="row row-offcanvas row-offcanvas-left" style="margin-top:4rem;">

	@include('components.pagelist')

	<div class="col-sm-12 row-oncanvas">

		@include('components.messages')

		this is the site page

		</div><!-- /.col -->

	</div><!-- /.row -->
</div>


@endsection
