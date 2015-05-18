var editor = ace.edit("problem");
//editor.setTheme();
editor.getSession().setMode("ace/mode/java");

//editor.setFontSize(18);

$(function(){
	
	var THEME = "ace/theme/twilight";
	
	
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
	
	//substitution
	$('body').on('click', 'button.s_insert', function(e){
		var id = $(this).closest('tr').find('input.s_name').val();
		//console.log('inserting ' + id);
		insertSubstution(id);
		e.preventDefault();
	});
	
	function insertSubstution(id){
		id = '`' + id + '`';
		replaceSelection(id);
	}
	
	$('button.s_new').click(function(e){
		e.preventDefault();
		var n = countSubs() + 1;
		var name = 's' + n;
		
		$(Mustache.render($('#s_new_tpl').html(), {name: name})).dialog({
			modal:true,
			buttons: {
				Create: function(){
					var name = $(this).find("#s_name_new").val();
					//var name = $('.ui-dialog #s_name_new').val();
					console.log(name);
					addSubstitutionField(name, '');
					if (!editor.selection.getRange().isEmpty())	
						insertSubstution(name);
					$(this).dialog('close');
					}
				}
			}
		);
		
	});
	
	function addSubstitutionField(name, value){
		var pos = countSubs() + 1;
		$('table.substitutions').append(renderSubstitution(name, value, pos))
	}
	
	function renderSubstitution(name, value, pos){
		return Mustache.render($('#s_row').html(), {name: name, value:value, pos: pos});
	}
	
	function replaceSelection(replace){
		editor.session.replace(editor.selection.getRange(), replace);
	}
	
	function countSubs(){
		return $('table.substitutions tr').length;
	}
	
	
	//Controls
	$('#fontsize').change(function(e){
		var val = $(e.target).val();
		editor.setFontSize(val);
		localStorage.setItem('fontSize', val);
	});
	$('#fontsize').val(localStorage.getItem('fontSize') || '12px');
	editor.setFontSize( $('#fontsize').val() );
	
	//Theme
	$('#theme').change(function(e){
		var val = $(e.target).val();
		editor.setTheme(val);
		localStorage.setItem('theme', val);
	});
	$('#theme').val(localStorage.getItem('theme') || THEME);
	editor.setTheme( $('#theme').val() );
	
	function loadSubstitutions(){
		//try{
			for (var i in subs){
				var s = subs[i];
				addSubstitutionField(s.name, s.value);
			}
		//}catch(e){}
	}
	
	loadSubstitutions();
	
	//populate filename
	if (''!=$('#q').val()){
		$('#filename').val( $('#q option:selected').text()  )	
	}
	
	
	
});
