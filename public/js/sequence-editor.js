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
    
    var base_url = $('#base_url').val();
    
    $('#save_btn').click(function(){
    	$.post(base_url + '/admin/process-sequence-editor', {}, function(res){
    		log(res);
    	}, 'json');
    });
	
	function log(msg){
		console && console.log && console.log(msg);
	}
	
})