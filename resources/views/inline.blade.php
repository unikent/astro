<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="../../favicon.ico">

	<title>Site Editor - University of Kent</title>

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
		'api_url' => config('editor.astro_api_url'),
		'logout_url' => config('editor.astro_logout_url'),
		'username' => $username,
		'user'     => $user,
		'api_token' => $api_token,
		'debug' => config('app.debug'),
		'published_url_pattern' => config('editor.app_live_url_pattern'),
		'draft_url_pattern' => config('editor.app_preview_url_pattern'),
		'assets_base_url' => config('definitions.assets_base_url'),
		'placeholder_image_url' => config('definitions.placeholder_image_url'),
		'help_url' => config('editor.help_url'),
		'help_media_url' => config('editor.help_media_url'),
		'auth_url' => config('editor.auth_url'),
	]); ?>;
	</script>

	@if (config('editor.enable_heap'))
		@include('components.heap-analytics')
	@endif

	@if (config('editor.enable_hotjar'))
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
