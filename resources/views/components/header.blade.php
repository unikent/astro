<div class="row">
	<div class="col-sm-12">

		<h1>{{ $site->title }}
			@if (isset($page))
				<small class="text-muted"><i class="kf-chevron-right"></i></small> {{ $page->title }}
			@endif
		</h1>

		<code>
			<a style="color: inherit;" target="_blank" href="{{ env('FRONTEND_URL') }}{{ isset($page) ? $page->path : $site->path }}">
				{{ env('FRONTEND_URL') }}{{ isset($page) ? $page->path : $site->path }}
			</a>
		</code>

	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<button type="button" class="btn btn-primary btn-sm" style="margin-top: 30px" data-toggle="offcanvas">Toggle nav</button>
	</div>
</div>