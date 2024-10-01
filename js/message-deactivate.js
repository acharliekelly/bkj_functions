window.onload = function(){
	jQuery(function ($) {
		jQuery('[data-slug="bkj-functions"] .deactivate a').on('click', function(event){
			//event.preventDefault()
			alert("Reminder: If you are using the 'class' taxonomy for styling, that may disappear when this plugin has been deactivated.")
		});
	})

}