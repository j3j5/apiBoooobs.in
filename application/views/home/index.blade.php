<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Laravel: A Framework For Web Artisans</title>
	<meta name="viewport" content="width=device-width">
	{{ HTML::style('laravel/css/style.css') }}
</head>
<body>
	<div class="wrapper">
		<header>
			<h1>Laravel</h1>
			<h2>A Framework For Web Artisans</h2>

			<p class="intro-text" style="margin-top: 45px;">
			</p>
		</header>
		<div role="main" class="main">
			<div class="home">
				<h2>Logged in?</h2>
				<?php
				if(Auth::check()) { ?>
					<strong>Logged in!!!!</strong> {{ HTML::link('connect/logout', 'Want to logout?') }}
				<?php } else { ?>
					<strong>NOT </strong> logged in!!!! {{ HTML::link('connect/session/twitter', 'Want to login?') }}
				<?php } ?>

			</div>
		</div>
	</div>
</body>
</html>
