$(function(){
		 
$('#advancedSearchToggle').change(function() {                      
	
	if($(this).prop('checked')){
			$('#advanced_search_content').show();
	}
	else{
		$('#advanced_search_content').hide();
	}
});

$('#advancedSearchToggle').prop('checked', false);
$('#advanced_search_content').hide();

});