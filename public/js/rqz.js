function showExplanation(identifier) {
	hint = $("#question-explanation-details-" + identifier).html();
	$("#question-hint-box").html( hint );
	$("#question-hint-box").show();
}


// @author Ivan Rodriguez
$(function(){
	//gray out pending rows from quiz listing
	$('td.pending').parent('tr').css('color', 'gray');
});