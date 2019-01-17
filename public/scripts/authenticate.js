window.onload = function () {
	linkAuthenticationPopUp();
};

function linkAuthenticationPopUp() {
	// Add function call to social login links
	let social_plugin_table = document.getElementById('authenticate-social-plugins');
	if (null === social_plugin_table) {
		// Required plugin table not available, skip
		return;
	}
	let social_plugin_target_link = social_plugin_table.dataset.authenticationUrl;
	let social_plugin_links = social_plugin_table.getElementsByClassName('authentication-social-plugin-link');
	for (let link_elem of social_plugin_links) {
		// Build authentication url
		link_elem.href = social_plugin_target_link + '?provider=' + link_elem.dataset.socialPlugin;
		link_elem.addEventListener('click', popupAuthenticationPopUp, false);
	}
}

function popupAuthenticationPopUp(event) {
	event.preventDefault();

	let link_elem = event.target.closest('a.authentication-social-plugin-link');
	let link_elem_href = link_elem.href;
	window.open(link_elem_href, 'SOCIAL_PLUGIN_WIndOW');
}

function success(token) {
	document.cookie = 'api_key=' + token;
	fetch('/api/v1/user/', {
		headers: {
			authorization: 'Bearer ' + token
		}
	})
		.then( function(response) {
			return response.json();
		})
		.then( function (data) {
			let social_plugin_div = document.getElementById('user-authentication-plugins');
			social_plugin_div.style.display = 'none';
			let user_data_div = document.getElementById('user-data');
			user_data_div.style.display = '';
			document.getElementById('user-data-id').textContent = data.id;
			document.getElementById('user-data-email').textContent = data.email;
			document.getElementById('user-data-name').textContent = data.name;
		});
}
