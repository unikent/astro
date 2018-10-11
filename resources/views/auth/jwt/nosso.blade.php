<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ url("/") }}/favicon.ico">

    <title>Site Editor - University of Kent</title>


    <link rel="stylesheet" href="{{ mix('/build/css/main.css') }}" />

    <script>
		window.Laravel = <?php echo json_encode([
			'csrfToken' => csrf_token(),
			'base' => url("/")
		]); ?>
    </script>
</head>

<body>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="mSGriK23brfDis2DuhU7NVvKkq1HY0rLLKXykdT7">

    <link rel="icon" href="../../favicon.ico">

    <title>Site Editor - University of Kent</title>

    <link rel="stylesheet" href="/build/css/main.css" />

</head>
<body class="custom-scrollbar vue-context">
<div id="editor">
    <div class="top-bar">
        <div>
            <span>
                Access to Site Editor denied
            </span>
        </div>
        <div class="top-bar__tools">
            <div class="user-account-dropdown__item user-account-dropdown__item--clickable">
                <form method="post" action="{{ config('editor.astro_logout_url') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="el-button el-button--primary pull-right"><strong>Sign out</strong></button>
                </form>
            </div>
        </div>
    </div>

    <div class="site-list">
        <div class="el-card">

            <div class="el-card__body">
                {!! config('editor.sso_denied_message') !!}
            </div>
        </div>
    </div>
</div>
</body>
</html>