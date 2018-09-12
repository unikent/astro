<html>
<head>
	<title>Login to astro</title>
</head>
<body>
<h1>Enter login credentials to log in</h1>

<form action="" method="post">
	{{ csrf_field() }}
	<label for="username">Username</label>
	<input type="text" name="username">
	<label for="lifetime">Password</label>
	<input type="password" name="password">
	<input type="submit" value="Log in">
</form>
</body>
</html>