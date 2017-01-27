<nav class="navbar navbar-toggleable-md navbar-light bg-faded mb-2">
	<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<a href="{{ URL::to('/') }}" class="navbar-brand"><i class="kf-star"></i> KentCMS</a>
	<div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" href="{{ URL::to('/') }}">Sites</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="{{ URL::to('media') }}">Media Library</a>
			</li>
		</ul>
		<span class="navbar-text">Site settings | Site permissions</span>
	</div>
</nav>