jQuery(document).ready(function($){
	var clipboard = new ClipboardJS('.emd-copy-clipb');
	clipboard.on('success', function(e) {
		var copies = document.getElementsByClassName("emd-copy-clipb");
		for(var i = 0; i < copies.length; i++)
		{
			copies.item(i).innerHTML = 'Copy';
		}
		e.trigger.innerHTML = 'Copied!';
	});
});
