var foundmessage = '';
function get_formtest_address() {
	var currentDomain = window.location.host;
	currentDomain = currentDomain.replace('www.', '');
	//var currentDomain = window.location.hostname;
	var atIndex = currentDomain.indexOf('.');
	var domainName = currentDomain.substring(0, atIndex);
	var emailAddress = "formtest+" + domainName + "@bkjproductions.com";
	console.log("Generated email address: " + emailAddress);
	return emailAddress;
}

jQuery(document).ready(function ($) {

	// Select all forms
	var allForms = $('form');

	// Variable to store the list of form IDs or names
	var formList = [];

	// Loop through each form
	allForms.each(function () {
		var currentForm = $(this);

		// Check if the form or any of its ancestors are hidden
		var hiddenAncestor = currentForm.closest(':hidden');

		// If a hidden ancestor is found, show it and the form itself
		if (hiddenAncestor.length > 0) {
			hiddenAncestor.slideDown(); // Or hiddenAncestor.fadeIn() for fading effect
			currentForm.slideDown(); // Or currentForm.fadeIn() for fading effect
		}
		else {
			// If the form or its ancestors are visible, add its ID or name to the formList array
			formList.push(currentForm.attr('id') || currentForm.attr('name'));
		}
	});

	// Display an alert with the number of forms found and their IDs or names
	foundmessage = 'Number of forms found: ' + allForms.length + '\nForms: ' + formList.join(', ');
	setTimeout(populate_form, 3000); // Adjust delay as needed



	function populate_form() {
		var formtest_address = get_formtest_address();

		// Fill out email field
		$('input[type="email"], input[name*="email"]').val(formtest_address);

		// Fill out phone number field
		$('input[type="tel"], input[name*="phone"]').val('781-662-8800');

		// Fill out message or comments field
		$('textarea[name*="message"], textarea[name*="comments"]').val('Just testing the form, please hit REPLY. Thanks!');

		// Fill out name field
		$('input[name*="your-name"], input[name*="firstname"], input[name*="name"]').val('BKJ Productions Support Team');

		// Fill out other fields with "test-" + field name
		$('input[aria-required="true"]:not([type="submit"]):not([type="hidden"]), textarea').each(function () {
			//	$('input:not([type="submit"]):not([type="hidden"]), textarea').each(function() {
			//$('input[type!="submit"], textarea').each(function() {
			if ($(this).val() === '') {
				var fieldName = $(this).attr('name');
				var fieldData = $(this).attr('placeholder');
				if (!fieldData) { fieldData = fieldName + ' sample'; }

				if (fieldName) {
					$(this).val('test-' + fieldData);
				}
			}
		});

		// Alert to review the fields before submitting
		alert(foundmessage + "\n" + 'Please review the fields and click submit to test the form. \nOur auto form fill occasionally gets flagged by recaptcha. If this happens, test forms again in an incognito window!');

	}

	// Function to check for the appearance of the element with data-elementor-type="popup"
	function checkForPopup() {
		// Check if the element exists
		if ($('[data-elementor-type="popup"]').length > 0) {
			// If the element exists, populate the form
			populate_form();
		} else {
			// If not, wait and check again after a short delay
			setTimeout(checkForPopup, 1000); // Adjust delay as needed
		}
	}

	// Call the function to start checking for the appearance of the element
	$(document).ready(function () {
		checkForPopup();
	});


});