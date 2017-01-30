<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="../../favicon.ico">

	<title>Kent CMS</title>

	<link rel="stylesheet" href="{{ mix('/css/app.css') }}"></link>
	<!-- TODO: move kent bar CSS into bundled dependencies -->
	<link rel="stylesheet" href="{{ url('/') }}/css/kent-bar.css"></link>

	<script>
		window.Laravel = <?php echo json_encode([
			'csrfToken' => csrf_token(),
		]); ?>
	</script>
</head>

<body>
	@include('components.kentbar')
	@include('components.menu')
	@yield('content')

	<script src="{{ mix('/js/manifest.js') }}"></script>
	<script src="{{ mix('/js/vendor.js') }}"></script>
	<script src="{{ mix('/js/app.js') }}"></script>
	<!-- TODO: move kent bar JS into bundled dependencies -->
	<script src="{{ url('/') }}/js/kent-bar.js"></script>
</body>
</html>
