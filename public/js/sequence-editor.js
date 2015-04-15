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
    
    $('button').click(function(){
    		$('#status').hide();$('#error').hide();		
    });
    
    var base_url = $('#base_url').val();
    
    $('#save_btn').click(function(){
    	$('#loader').show();
    	
    	var items = [];
    	$('#current option').each(function(){items.push($(this).val())})
    	
    	var data = {id: $('#id').val(),
			    		items: items
			    	};
    	
    	//log(data); return;
    	$.post(base_url + '/admin/process-sequence-editor', data, function(res){
    		log(res);
    		if(res && res.result=='ok'){
    			$('#status').html(res.msg).show();
    		}else{
    			var error = res.msg || 'Server Error. Please contact support.';
    			$('#error').html(error).show();
    		}
    			
    	}, 'json')
    	.always(function(){
    		$('#loader').hide();	
    	})
    	;
    });
	
    $('#loader').hide();
    
	function log(msg){
		console && console.log && console.log(msg);
	}
	
})