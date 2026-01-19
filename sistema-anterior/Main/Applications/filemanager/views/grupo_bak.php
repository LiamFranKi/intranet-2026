{% if grupo.validFileManagerToken(get.token) %}
<script>
var fileManager;
function fillFilesOnTable(files){
	$('#filesLoading').hide();
	$('#fileManagerFilesList').find('tr').remove();
	for(i in files){
		file = files[i];
		data = '<tr>';
		{% if get.selectFiles == 'true' %}
		data += '<td class="center">'+ (file.type == 'File' ? '<input type="checkbox" onclick="fileManager.selectFile(\''+ file.filePath +'\', \''+ file.filename +'\')" '+ (localStorage.getItem(file.filePath) != null ? 'checked' : '') +' />' : '') +'</td>';
		{% endif %}
		data += '<td class="center">'+ (file.type == 'Folder' ? '{{ icon('folder') }}' : '{{ icon('page') }}') +'</td>'+
		'<td><a href="'+ (file.type == 'File' ? '/filemanager/download?f=' + file.filePath : 'javascript:;') +'" onclick="'+ (file.type == 'Folder' ? 'fileManager.setBase(\''+ file.folderBase +'\', fillFilesOnTable)' : '') +'">'+ file.filename +'</a></td>'+
		'<td class="center">'+ file.size +'</td>'+
		'<td class="center">'+ file.modifiedDate +'</td>';
		{% if permissions.DELETE %}
		if(file.filename != '..'){
			data += '<td class="center"><a href="javascript:;" onclick="fileManager.deleteFile(\''+ file.filePath +'\')">{{ icon('delete') }}</a></td>';
		}else{
			data += '<td class="center"></td>';
		}
		{% endif %}
		data += '</tr>';

		$('#fileManagerFilesList').append(data);
	}
}
$(function(){
	fileManager = {
		token: '{{ get.token }}',
		base: '{{ base64_encode(grupo.getFileManagerDirectory()) }}',
		getFiles: function(callback){
			$('#filesLoading').show();
			$.post('/filemanager/group_files', {token: fileManager.token, base: fileManager.base, grupo_id: '{{ grupo.id }}'}, callback, 'json');
		},
		setBase: function(base, callback){
			fileManager.base = base;
			console.log(base);
			fileManager.getFiles(callback);
		},
		selectFile: function(filePath, fileName){
			if(localStorage.getItem(filePath) == null){
				localStorage.setItem(filePath, fileName);
			}else{
				localStorage.removeItem(filePath);
			}
		},
		deleteFile: function(filePath){
			$.prompt({
				state0: {
					html: '¿Está seguro de borrar el archivo?',
					buttons: {
						"Si": true,
						"No": false
					},
					submit: function(e, v){
						e.preventDefault();
						if(v){
							$('#filesLoading').show();
							$.post('/filemanager/delete', {file: filePath}, function(r){
								if(parseInt(r[0]) == 1){
									fileManager.getFiles(fillFilesOnTable);

									$.prompt.close();
								}else{
									$.prompt.goToState('fail');
								}
							}, 'json');
						}else{
							$.prompt.close();
						}
					}
				},
				fail: {
					html: 'No se pudo borrar el archivo.'
				}
				
			});
		},
		makeFolder: function(){
			$.prompt({
				state0: {
					html: '<p>Ingrese el nombre de la carpeta:</p><input type="text" id="fileManagerFolderName" class="form-control input-sm" />',
					buttons: {
						"Crear Carpeta": true,
						"Cancelar": false
					},
					submit: function(e, v){
						e.preventDefault();
						if(v){
							$.post('/filemanager/make_group_folder', {base: fileManager.base, name: $('#fileManagerFolderName').val(), grupo_id: '{{ grupo.id }}'}, function(r){
								if(parseInt(r[0]) == 1){
									fileManager.getFiles(fillFilesOnTable);
									$.prompt.close();
								}else{
									$.prompt.goToState('fail');
								}
							}, 'json');
						}else{
							$.prompt.close();
						}
					}
				},
				fail: {
					html: 'No se pudo crear la carpeta.'
				}
				
			});
		}
	}

	fileManager.getFiles(fillFilesOnTable);
	
	{% if permissions.UPLOAD %}
	$('#filesHandler').fileupload({
	    url: '/filemanager/group_upload',
        dataType: 'json',
        maxChunkSize: 1000000,
        sequentialUploads: true,
        done: function (e, data) {
            if(data.result[0] == 0){
				return $.prompt('No se pudo subir el archivo: ' + data.files[0].name);
			}
        },
        add: function(e, data){
			data.formData = {base: fileManager.base, grupo_id: '{{ grupo.id }}'}
			data.submit();
			$('#uploader').show();
		},
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#fileUploadProgress').data('aria-valuenow', progress);
        $('#fileUploadProgress').css(
            'width',
            progress + '%'
        );

        if(progress >= 100){
        	fileManager.getFiles(fillFilesOnTable);
        	$('#uploader').hide();
        	$('#fileUploadProgress').data('aria-valuenow', 1);
	        $('#fileUploadProgress').css(
	            'width',
	            '1%'
	        );
        }
    }).on('fileuploaddone', function(e, data){
    	
    });

    {% endif %}

});
</script>
<div class="menu">
	{% if get.selectFiles == "true" %}
	<button class="btn-default btn" onclick="{{ get.backUrl ? 'fancybox(\''~ get.backUrl|raw ~'\')' : '$.fancybox.back()' }}">{{ icon('list') }} Volver Atrás</button>
	{% endif %}
	{% if get.backMySpace == "true" %}
	<button class="btn-default btn" onclick="$.fancybox.back()">{{ icon('list') }} Volver Atrás</button>
	{% endif %}
	{% if permissions.UPLOAD %}
	<button class="btn-default btn" onclick="$('#filesHandler').click()">{{ icon('application_get') }} Subir Archivos</button>
	{% endif %}
	{% if permissions.CREATE %}
	<button class="btn-default btn" onclick="fileManager.makeFolder()">{{ icon('folder') }} Crear Carpeta</button>
	{% endif %}
	{% if grupo.enlace_archivos %}
	<a class="btn-default btn" href="{{ grupo.enlace_archivos }}" target="_blank">{{ icon('folder_go') }} Otros Archivos</a>
	{% endif %}
</div>
{% if permissions.UPLOAD %}
<div class="block" id="uploader" style="display: none">
	<h2>Subiendo Archivos</h2>
	<input type="file" id="filesHandler" name="files" style="display: none" multiple />
	<!--<table class="special" id="uploadsList"></table>-->
	
	<div class="progress">
      <div class="progress-bar progress-bar-striped active" id="fileUploadProgress" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">Progress</span></div>
    </div>
	
</div>
{% endif %}
<div class="block" style="width: 600px; padding-bottom: 20px">
	<h2>Administrador de Archivos</h2>
	{% if get.selectFiles == 'true' %}
	<div class="alert alert-info center">Marque los archivos que desee compartir, y luego haga click en "Volver Atrás"</div>
	{% endif %}
	<table id="larchivos" class="dataTable table table-striped table-bordered table-hover">
		<thead>

			{% if get.selectFiles == 'true' %}
				<th style="width: 40px"></th>
			{% endif %}

			<th style="width: 40px"></th>
			<th>Archivo</th>
			<th style="width: 60px">Tamaño</th>
			<th style="width: 100px">Modificado</th>
			{% if permissions.DELETE %}
				<th style="width: 40px"></th>
			{% endif %}
		</thead>
		<tbody id="fileManagerFilesList"></tbody>
	</table>
	<div id="filesLoading" class="formLoading"></div>
</div>
{% else %}
<script type="text/javascript">
$(function(){
	alert('No tiene los permisos suficientes');
	$.fancybox.close();
});
</script>
{% endif %}