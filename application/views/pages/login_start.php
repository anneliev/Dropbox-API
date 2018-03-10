<?php

echo 
'
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dropbox application</title>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../../css/style.css">
	
	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

</head>
<body>
	<div class="container">
		<div class="row" id="sign_btns">
			
			<div class="col-2">
				<br />
				<img height="60em" width="60em" src="../../images/dropbox_logo.png" alt="Dropbox logo" />
			</div>
			<div class="col-4" id="signin_btn">
				<br />
				<a href="' . $authUrl . '" class="btn btn-outline-primary btn-lg" role="button">Sign In</a>
				</div>
				<div class="col-4" id="signout_btn">
				<br />
				<a href="http://localhost:8888/Dropbox-API/index.php/login/sign_out" class="btn btn-outline-primary btn-lg">Sign Out</a>
			</div>
			<div class="col-2">
			</div>
		</div>
	</div>
</body>
</html>
';
