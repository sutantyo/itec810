/**
 * Sequence editor
 * @author Ivan Rodriguez 
 */

$(function(){
	
	
	$('#add_btn').click(function(){
		//log("sup");
		return !$('#available option:selected').remove().appendTo('#current'); 
	})
	
	$('#remove_btn').click(function(){
		//log("sup");
		return !$('#current option:selected').remove().appendTo('#available'); 
	})
	
	function log(msg){
		console && console.log && console.log(msg);
	}
	
})