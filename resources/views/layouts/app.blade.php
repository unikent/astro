<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="{{ url("/") }}/favicon.ico">

	<title>Kent CMS</title>

	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/css/app.css') }}"></link>
	<!-- TODO: move kent bar CSS into bundled dependencies -->
	<link rel="stylesheet" href="{{ url('/') }}/css/kent-bar.css"></link>

	<script>
		window.Laravel = <?php echo json_encode([
			'csrfToken' => csrf_token(),
			'base' => url("/")
		]); ?>
	</script>
</head>

<body>
	<!-- @include('components.menu') -->
	@yield('content')

	<script src="{{ url('/') }}{{ mix('js/manifest.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('js/vendor.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('js/app.js') }}"></script>
	<!-- TODO: move kent bar JS into bundled dependencies -->

</body>
</html>
