var editor = ace.edit("problem");
editor.setTheme("ace/theme/twilight");
editor.getSession().setMode("ace/mode/java");

$(function(){
	
	$('#editor-form').on('submit',function(evt){
		var data = $(evt.target).serializeArray();
		
		//retrieve the code
		data.push({name:'problem', value: editor.getValue() });
		
		saveTemplate(data);
		
		evt.preventDefault();
	});
	
	function saveTemplate(data){
		log(data);
		data.push({name:'method', value:'saveTemplate'});
		var url = $('#base_url').val() + '/question-template-editor/ajax';
		$.post(url, data, function(res){
			handleRes(res);
		}, 'json');
	}
	
	function handleRes(res){
		if(!res || (res.result && res.result=='error') ){
			showResult('Error', (res && res.msg) || 'Internal Server Error' ); 
		}
		else{
			showResult(res.result, res.msg);	
		}
		
	}
	
	function log(msg){
		try{console.log(msg)}catch(e){};
	}
	
	function showResult(title, msg){
		$('<div title="' + title + '"><p>' + msg +'</p></div>')
			.dialog(
			{
				modal: true,
		        buttons: {
		            Ok: function () {
		                $(this).dialog('close');
		            }
		        }
			}		
		);
		//$('#dialog-message').remove();
	}
	
});
