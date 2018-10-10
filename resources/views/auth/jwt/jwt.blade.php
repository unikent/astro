<html>
<script>
	// self executing function here
	(function() {
		console.log('sending message');
		parent.postMessage(<?php echo json_encode(['jwt' => $jwt])?>, '<?php echo config('app.url') ?>');
		console.log('message sent');
	})();
</script>
</html>
