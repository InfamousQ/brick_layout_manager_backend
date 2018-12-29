<?php
	$this->layout('layout::layout_home', ['title' => 'Home']);
?>
<p>Brick layout manager - home page </p>

<div>
	<div id="user-data" style="display:none">
		<h2>Profile</h2>
		<p><b>Hello, <span id="user-data-name"></span>!</b></p>
		<p>Your email is <span id="user-data-email"></span></p>
		<p>Your LManager id is <span id="user-data-id"></span></p>
		<p><a href="user/logout">Logout</a></p>
	</div>
	<div id="user-authentication-plugins">
		<h2>Social networks</h2>
		<table id="authenticate-social-plugins" data-authentication-url="/user/authenticate">
			<tr>
				<td><a class="authentication-social-plugin-link" href="#" data-social-plugin="Facebook"><img alt="Login with Facebook" src="https://cdnjs.cloudflare.com/ajax/libs/webicons/2.0.0/webicons/webicon-facebook.png"></a></td>
				<td><a class="authentication-social-plugin-link" href="#" data-social-plugin="Github"><img alt="Login with Github" src="https://cdnjs.cloudflare.com/ajax/libs/webicons/2.0.0/webicons/webicon-github.png" /></a></td>
			</tr>
		</table>
	</div>
</div>