@extends('layouts.app')

@section('content')

<div class="container">
	<div class="row">
		<div class="col-md-8 offset-md-2">
			<div class="card">
				<div class="card-header card-info card-inverse">Login</div>
				<div class="card-block">
					<?php if (isset($errors) && count($errors) > 0): ?>
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								<?php foreach ($errors->all() as $error): ?>
									<li><?=$error?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<form class="form-horizontal" role="form" method="POST" action="<?php echo url("/auth/login"); ?>">
						<input type="hidden" name="_token" value="<?=csrf_token();?>">

						<div class="form-group">
							<label class="col-md-6 control-label">Username</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="username" value="<?=old('username');?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-6 control-label">Password</label>
							<div class="col-md-6">
								<input type="password" class="form-control" name="password">
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary" style="margin-right: 15px;">
									Login
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


@endsection