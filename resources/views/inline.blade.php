<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="../../favicon.ico">

	<title>Astro</title>

	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/build/css/vendor.css') }}" />
	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/build/css/main.css') }}" />
	@if ($is_preview)
		<link rel="stylesheet" href="https://static.kent.ac.uk/pantheon/kent-theme-assets/assets/css/main.min.css" />
		<link rel="stylesheet" href="https://static.kent.ac.uk/pantheon/kent-theme-assets/assets/css/kentfont.css" />
		<style>
		html {
			background-color: #f7f7f7;
		}
		.b-block {
			max-width: calc(100vw - 17px);
		}
		</style>
	@endif

	<script>
	window.astro = <?php echo json_encode([
		'csrf_token' => csrf_token(),
		'base_url' => Request::getBaseUrl(),
		'api_url' => '/api/v1/',
		'username' => $user,
		'api_token' => $api_token,
		'debug' => config('app.debug')
	]); ?>;
	window.isEditor = <?php echo json_encode(!$is_preview); ?>;
	</script>
</head>
<body class="custom-scrollbar">
	<div id="editor">

	</div>

	<script src="{{ url('/') }}{{ mix('/build/js/manifest.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('/build/js/vendor.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('/build/js/main.js') }}"></script>
</body>
</html>
