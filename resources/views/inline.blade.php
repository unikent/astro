<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="../../favicon.ico">

	<title>Astro</title>

	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/css/app.css') }}" />
	@if ($route === 'preview')
		<link rel="stylesheet" href="{{ url("/") }}/css/main.min.css" />
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
	window.Laravel = <?php echo json_encode([
		'csrfToken' => csrf_token(),
		'base' => Request::getBaseUrl(),
		'username' => $user
	]); ?>;
	window.isEditor = <?php echo json_encode($route !== 'preview'); ?>;
	</script>
</head>
<body class="custom-scrollbar">
	<div id="editor">

	</div>

	<script src="{{ url("/") }}{{ mix('js/manifest.js') }}"></script>
	<script src="{{ url("/") }}{{ mix('js/vendor.js') }}"></script>
	<script src="{{ url("/") }}{{ mix('js/app.js') }}"></script>
</body>
</html>
