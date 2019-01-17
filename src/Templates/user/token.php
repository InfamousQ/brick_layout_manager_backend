<!DOCTYPE html>
<html>
<head>
	<title>Popup to receive token from API and forward to main page</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script>
		// Call the original page with the token received from our API
		var token = location.search.replace('?token=', '');
		window.opener.success(token);

		// Close this popup
		window.close();
	</script>
</head>
<body>
We logged in via a third party provider, and gave our API a token. This
token will now be sent to the main page in order to be injected in
future API calls.
</body>
</html>