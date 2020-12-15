<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<title>Contact &mdash; Acme</title>
		<link rel="stylesheet" href="assets/css/style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/history.js/1.8/bundled/html4+html5/jquery.history.js"></script>


	</head>

	<body>
<div class="site" id="wrap">
	<header class="site-header" id="masthead">
		<nav class="nav container">
			<ul class="nav__menu" id="nav-primary">
				<li class="nav__menu-item home">
					<a class="nav__menu-link page-link" href="./" rel="home">Acme</a>
				</li>
				<li class="nav__menu-item">
					<a class="nav__menu-link page-link" href="products.html" title="Products">Products</a>
				</li>
				<li class="nav__menu-item">
					<a class="nav__menu-link page-link" href="about.html" title="About Us">About</a>
				</li>
				<li class="nav__menu-item">
					<a class="nav__menu-link page-link" href="contact.html" title="Contact Us">Contact</a>
				</li>
			</ul>
			<ul class="nav__menu" id="nav-secondary">
				<li class="nav__menu-item sign-in">
					<a class="nav__menu-link page-link" href="sign-in.html" title="Sign-in">Sign-in</a>
				</li>
				<li class="nav__menu-item sign-up">
					<a class="nav__menu-link page-link" href="sign-up.html" title="Sign-up">Sign-Up</a>
				</li>
			</ul>
		</nav>
	</header>
	<div class="site-main" id="main">	<section class="section contact">
		<header class="section__header animated fadeIn">
			<div class="container">
				<h1 class="section__heading">Contact Us</h1>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorem, asperiores nisi.</p>
			</div>
		</header>
		<div class="container section__col animated fadeIn">
			<form class="contact__form" action="" method="post">
				<div>
					<label class="contact__form-label" for="full-name">Name</label>
					<input class="contact__form-field" type="text" id="full-name" placeholder="e.g. John Doe">
				</div>
				<div>
					<label class="contact__form-label" for="email-address">E-mail</label>
					<input class="contact__form-field" type="email" id="email-address" placeholder="e.g. john@gmail.com">
				</div>
				<div>
					<label class="contact__form-label" for="message">Message</label>
					<textarea class="contact__form-field" id="message" rows="15"></textarea>
				</div>
				<div>
					<button class="btn">Send</button>
				</div>
			</form>
		</div>
	</section>
	</div> <!-- /.site-main  -->
	
	<div class="subscribe">
		<div class="container">
			<h2>Lorem ipsum dolor sit amet.</h2>
			<form class="subscribe-form" action="post">
				<input class="subscribe-form__field" type="email" placeholder="Your email address...">
				<button class="subscribe-form__button btn btn--primary">Subscribe</button>
			</form>
		</div>
	</div>

	<footer class="site-footer" id="footer">
		<nav class="nav container">
			<ul class="nav__menu" id="menu-footer">
				<li class="nav__menu-item"><a class="nav__menu-link page-link" href="tos.html" title="Terms of Service">Terms</a></li>
				<li class="nav__menu-item"><a class="nav__menu-link page-link" href="privacy.html" title="Privacy Policy">Privacy</a></li>
				<li class="nav__menu-item"><a class="nav__menu-link page-link" href="faq.html" title="Frequently Asked Questions">FAQ</a></li>
			</ul>
		</nav>
		<div class="footnote container">
			<p class="footnote">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti quibusdam est quos quidem aliquid quae atque necessitatibus labore reprehenderit ducimus repellendus.</p>
		</div>
	</footer>
</div>
	<script src="assets/js/script.js"></script>
	</body>
</html>