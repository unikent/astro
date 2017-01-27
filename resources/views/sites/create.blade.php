@extends('layouts.app')

@section('content')

<div class="container-fluid">

	@include('components.header')

	<div class="row row-offcanvas row-offcanvas-left" style="margin-top:4rem;">

		@include('components.pagelist')

		<div class="col-sm-12 row-oncanvas">

			@include('components.messages')

			{!! Form::open(array('route' => array('site.page.store', $site), 'class' => 'form')) !!}

			<div class="card card-default">
				<div class="card-header">
					<h3 class="card-title">Page details</h3>
				</div>
				<div class="card-block">


					<div class="form-group">
							{!! Form::label('Title') !!}
							{!! Form::text('title', null,
									array('required',
												'class'=>'form-control'
									)) !!}
							<p class="form-text text-muted">
								The title of your page.
							</p>
					</div>

					<div class="form-group">
							{!! Form::label('Slug') !!}
							{!! Form::text('path', null,
									array('class'=>'form-control'
									)) !!}
							<p class="form-text text-muted">
								Leave this blank if you want the system to create a page identifier for you.
							</p>
					</div>

					<div class="form-group">
							{!! Form::label('Options') !!}
							{!! Form::text('options', null,
									array('class'=>'form-control'
									)) !!}
					</div>

				</div>
			</div>

			<div class="form-group">
					{!! Form::submit('Save',
						array('class'=>'btn btn-success'
					)) !!}
			</div>


			{!! Form::close() !!}

		</div><!-- /.col -->

	</div><!-- /.row -->
</div>

@endsection
