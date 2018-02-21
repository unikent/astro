<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="../../favicon.ico">

	<title>Astro</title>

	<link rel="stylesheet" href="{{ mix('/build/css/main.css') }}" />
	@if ($is_preview)
		<style>
		html {
			background-color: #f7f7f7;
		}
		</style>
	@endif

	<script>
	window.astro = <?php echo json_encode([
		'csrf_token' => csrf_token(),
		'base_url' => Request::getBaseUrl(),
		'api_url' => '/api/v1/',
		'username' => $username,
		'user'     => $user,
		'api_token' => $api_token,
		'debug' => config('app.debug')
	]); ?>;
	</script>

	@if (env('ENABLE_HEAP'))
		@include('components.heap-analytics')
	@endif

	@if (env('ENABLE_HOTJAR'))
		@include('components.hotjar-analytics')
	@endif

</head>
<body class="custom-scrollbar{{ $is_preview ? '' : ' vue-context' }}">
	<div id="editor">

	</div>

	<script src="{{ mix('/build/js/manifest.js') }}"></script>
	<script src="{{ mix('/build/js/vendor.js') }}"></script>
	<script src="{{ mix('/build/js/main.js') }}"></script>
</body>
</html>
