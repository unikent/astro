<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" href="{{ url("/") }}/favicon.ico">

	<title>Kent CMS</title>

	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/build/css/vendor.css') }}" />
	<link rel="stylesheet" href="{{ url("/") }}{{ mix('/build/css/main.css') }}" />

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

	<script src="{{ url('/') }}{{ mix('/build/js/manifest.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('/build/js/vendor.js') }}"></script>
	<script src="{{ url('/') }}{{ mix('/build/js/main.js') }}"></script>
	<!-- TODO: move kent bar JS into bundled dependencies -->

</body>
</html>
