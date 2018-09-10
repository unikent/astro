<html>
<head>
	<title>Generate a Dev Token</title>
</head>
<body>
<h1>Enter a name and expiry time to generate a JWT</h1>
<p>To expire an existing token, append ?reset to the URL of this page.</p>
<form action="" method="post">
	{{ csrf_field() }}
	<label for="username">Username</label>
	<input type="text" name="jwt_username">
	<label for="lifetime">Lifetime (in seconds)</label>
	<input type="text" name="jwt_lifetime">
	<input type="submit" value="Create Token">
</form>
</body>
</html>