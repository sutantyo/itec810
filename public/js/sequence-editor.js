/**
 * Sequence editor
 * @author Ivan Rodriguez 
 */

$(function(){
	
	
	$('#add_btn').click(function(){
		//log("sup");
		return !$('#available option:selected').remove().appendTo('#current'); 
	});
	
	$('#remove_btn').click(function(){
		//log("sup");
		return !$('#current option:selected').remove().appendTo('#available'); 
	});
	
    $('.position_buttons button').click(function(){
        var $op = $('#current option:selected'),
            $this = $(this);
        if($op.length){
            ($this.data('dir') == 'up') ? 
                $op.first().prev().before($op) : 
                $op.last().next().after($op);
        }
    });
	
	function log(msg){
		console && console.log && console.log(msg);
	}
	
})