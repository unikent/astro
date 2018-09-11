<html>
<head>
	<?php
	$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjE1MzYzMjEwMTUxNDUwOCJ9.eyJqdGkiOiIxNTM2MzIxMDE1MTQ1MDgiLCJpYXQiOjE1MzYzMjEwMTUsIm5iZiI6MTUzNjMyMTAxNSwiZXhwIjoxNTM2MzIxMzE1LCJ1aWQiOiJhZG1pbiJ9.xQI7CBAteAvmyomAvvUKrYwh5cW0kDGrI58sTZ8dGyM";
	?>
</head>
<script>
	// self executing function here
	(function() {
		console.log('sending message');
		parent.postMessage(<?php echo json_encode(['jwt' => $token])?>, 'https://siteeditor.test');
		console.log('message sent');
	})();
</script>
</html>
