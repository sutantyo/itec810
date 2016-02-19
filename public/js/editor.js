var editor = ace.edit("problem");
//editor.setTheme();
editor.getSession().setMode("ace/mode/java");

//editor.setFontSize(18);

$(function(){

	var THEME = "ace/theme/twilight";


	$('#editor-form').on('submit',function(evt){
		evt.preventDefault();
	});

	//Explicit save
	$('.save').click(function(){
		log('save button clicked');
		var data = $('#editor-form').serializeArray();

		//retrieve the code
		data.push({name:'problem', value: editor.getValue() });
		saveTemplate(data);
		log('finished click action');
	});


	function saveTemplate(data){
		log("in editor.js saveTemplate()");
		//log(data);
		data.push({name:'method', value:'saveTemplate'});
		var url = baseUrl() + '/question-template-editor/ajax';
		var d = placeLoader();
		$.post(url, data, function(res){
			d.dialog('destroy');
			handleRes(res);
		}, 'json')
		.done(function(){
			log("Done");
		})
		.fail(function(err){
			log(err);
		});
	}

	function handleRes(res){
		if(!res || (res.result && res.result=='error') ){
			showResult('Error', (res && res.msg) || 'Internal Server Error' );
		}
		else{
			showResult(res.title || res.result, res.msg);
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

	function placeLoader(title, msg){
		title = title || 'Please wait ...';
		msg = '<img src="' + baseUrl() + '/images/loader.gif" >';
		return $('<div title="' + title + '"><p>' + msg +'</p></div>')
			.dialog(
			{
				modal: true
			}
		);
		//$('#dialog-message').remove();
	}

	//Add substitution placeholder into the editor
	$('body').on('click', 'button.s_insert', function(e){
		var id = $(this).closest('tr').find('input.s_name').val();
		//console.log('inserting ' + id);
		insertSubstution(id);
		e.preventDefault();
	});

	function insertSubstution(id){
		replaceSelection(id_for(id));
	}

	function id_for(id){
		return '`' + id + '`';
	}

	//Create new substitution
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

	// Remove substitution
	$('body').on('click', 'button.s_delete', function(e){
		var id = $(this).closest('tr').find('input.s_name').val();

		if (editor.findAll(id_for(id)) > 0   ){
			if ( !confirm('Substitution exist in editor. Delete anyway?') ){
				return;
			}
		}

		$(this).closest('tr').remove();

		e.preventDefault();
	});


	//Highlight placeholder
	$('body').on('focus', 'input.s_name', function(e){
		var id = $(this).closest('tr').find('input.s_name').val();
		editor.findAll(id_for(id));
	});

	/*
	$(document).on('keydown', 'input.s_name', function(e){
		var el = $(e.target);
		el.data('old', el.val());
	});

	$(document).on('keyup', 'input.s_name', function(e){
		var el = $(e.target);
		var old = el.data('old');
		var next = el.val();
		console.log(old+ " -> "+ next  );
		editor.replaceAll(id_for(old), id_for(next));
	});
	*/

	//Opens a new window to test the complilation of the current template
	$('button.test').click(function(e){
		e.preventDefault();
		var url = baseUrl() + '/index/testquestiongeneration?q=';
		var file = $('#filename').val();
		if (!file.trim()){
			alert("Save first");
			return;
		}

		window.open(url + file.replace('.xml', ''), '_blank');

	});

	$('button.quality').click(function(e){
		e.preventDefault();
		var url = baseUrl() + '/question-template-editor/ajax';
		var file = $('#filename').val().replace('.xml', '').trim();
		if (!file){
			alert("Save first");
			return;
		}

		data = {file: file,
				method: 'qualityTest',
			}

		var d = placeLoader();

		$.post(url, data, function(res){
			d.dialog("destroy");
			handleRes(res);
		}, 'json');

	});

	function baseUrl(){
		return $('#base_url').val();
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

	// Added by Daniel Sutantyo
	$('.back').click(function(e){
		window.location.replace("/");
	});


});
