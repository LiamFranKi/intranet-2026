var zk = {
    fancyboxHistory: [],
    fancyboxIndex: -1,
    router: null,
    currentUrl: null,
    startRouter: function(){
        //zk.router = new AppRouter;
        //zk.router.on('route:mainRouteHandler', function(url, queryString) {
		$(window).on('hashchange', function(){
			let url = window.location.hash.substring(1);
			
            if(url != null){
                zk.currentUrl = url ;
				/*
                if(queryString != null){
                    zk.currentUrl += '?'+ queryString;
                }
				*/
                $.ajax({
                    url: zk.currentUrl,
                    type: 'GET',
                    beforeSend: function(){
                        //$.niftyAside("hide");
                        //$.niftyNav('expand');
                        $(window).scrollTop(0);
						$('#container').removeClass('page-fixedbar')

						if($('#content-container-left #page-content').length > 0)
							$('#content-container-left').niftyOverlay('show');
						else
							$('#content-container-left').html(`
								<div id="page-head">
									<div id="page-title">
										<h1 class="page-header text-overflow">Cargando...</h1>
									</div>
								
								</div>
								<div id="page-content">
									<div class="panel">
										<div class="panel-body text-center">
											<div class="panel-overlay-content pad-all unselectable"><span class="panel-overlay-icon text-main"><i class="fa fa-refresh fa-spin fa-2x"></i></span><h4 class="panel-overlay-title"></h4><p></p></div>
										</div>
									</div>
								</div>
							`)

						
                    },
                    data: null,
                    dataType: 'html',
                    success: function(response){
                        $('#content-container-left').html(response);
                    },
                    complete: function(){
						$('#content-container-left').niftyOverlay('hide');
                    },
                    error: function(){
                       

						zk.pageAlert({message: 'No se pudo realizar la operación', title: 'Error de carga', icon: 'bolt', type: 'danger', container: '#content-container', timer: 3000});
                    }
                });
            }
        });

        //Backbone.history.start();
    },
    reloadPage: function(){
        //Backbone.history.loadUrl();
		$(window).trigger('hashchange');
    },
    goToUrl: function(url){
        if(url == zk.currentUrl){
            return zk.reloadPage();
        }
		window.location.hash = url;
		//$(window).trigger('hashchange');
        //zk.router.navigate("/" + url);
    },
    printDocument: function (url, width, height) {
        if(!width) width = 800;
        if(!height) height = 600;
        window.open(url, '_blank', 'width='+ width +',height=' + height);
    },
    formErrors: function(form, errors){
        for(i in errors){
            $(form).data('bootstrapValidator').updateStatus(i, 'INVALID');
            $('[data-bv-for="'+ i +'"]', $(form)).html(errors[i][0]);
        }
        return false;
    },
    confirm: function(message, callback, callbackCancel){
        $.prompt(message, {
            submit: function(e, v){
                e.preventDefault();
                if(v){
                    if(callback) callback();
                }else{
                    if(callbackCancel) callbackCancel();
                }

                $.prompt.close();
            },
            buttons: {
                'Si': true,
                'No': false
            }
        });
    },
    sendData: function(url, data, callback, container){
        //_form = this;
        options = {
            url: url,
            type: 'POST',
            beforeSend: function(){
                if(!container)
				$('#content-container').niftyOverlay('show');
                else
                    $(container).niftyOverlay('show');
            },
            
            data: data,
            dataType: 'json',
            success: function(r){
                if(callback) return callback(r);
            },
            complete: function(){
                if(!container)
				$('#content-container').niftyOverlay('hide');
                else
                    $(container).niftyOverlay('hide');
              
            },
            error: function(){
                alert('No se pudo realizar la petición');
            }
        };
        if(data.toString() == '[object FormData]'){
            options['processData'] = false;
            options['contentType'] = false;
            options['dataType'] = 'json';
        }

        $.ajax(options);
    },
    searchForm: function(url, data, container, callback){
        _form = this;
        $.ajax({
            url: url,
            type: 'POST',
            beforeSend: function(){
                $(container).niftyOverlay('show');
            },
            
            data: data,
            
            
            success: function(r){
                $(container).html(r);
                if(callback){
                    callback(r);
                }
            },
            complete: function(){
               $(container).niftyOverlay('hide');
            },
            error: function(){
                alert('No se pudo realizar la petición');
            }
        });
    },
    pageAlert: function(options){
        options = $.extend({
            type: 'info',
            message: '',
            title: '',
            timer: 3000,
            icon: '',
            container: 'page',
            closebtn: true,
            floating_position: 'top-right'
        }, options);

        var html = '';
        if(options.icon != ''){
            html += '<div class="media-left">\
                <span class="icon-wrap icon-wrap-xs icon-circle alert-icon">\
                    <i class="fa fa-'+ options.icon +' fa-lg"></i>\
                </span>\
            </div>';
        }
        html += '<div class="media-body">';
        if(options.title != ''){
            html += '<h4 class="alert-title">'+ options.title +'</h4>';
        }
        html += '<p class="alert-message">'+ options.message +'</p>\
            </div>';
        /*
        $.niftyNoty({
            type: options.type,
            container : options.container,
            html : html,
            timer : options.timer
        });
        */

        $.niftyNoty({
            type: options.type,
            container: options.container,
            html: html,
            closeBtn: options.closebtn,
            floating: {
                position: options.floating_position,
                animationIn: 'jellyIn',
				animationOut: 'fadeOut'
				
            },
            focus: true,
            timer: options.timer
        });
    },
    filterDPD: function(_data){
        _data = $.extend({
            departamento: {
                field: '#departamento_id',
            },
            provincia: {
                field: '#provincia_id',
                value: ''
            },
            distrito: {
                field: '#distrito_id',
                value: ''
            },
        }, _data);
        
        // PROVINCIA
        $(_data.provincia.field).bind('change', function(){
            $(_data.distrito.field).find('.item').remove();
            $.post('/info/distritos', {provincia_id: $(_data.provincia.field).val()}, function(r){
                for(i in r){
                    distrito = r[i];
                    $(_data.distrito.field).append('<option value="'+ distrito.id +'" class="item" '+ (distrito.id == _data.distrito.value ? 'selected' : '') +'>'+ distrito.nombre +'</option>');
                }
                
            }, 'json');
        });
        // DEPARTAMENTO
        $(_data.departamento.field).bind('change', function(){
            $(_data.provincia.field).find('.item').remove();
            $.post('/info/provincias', {departamento_id: $(_data.departamento.field).val()}, function(r){
                for(i in r){
                    provincia = r[i];
                    $(_data.provincia.field).append('<option value="'+ provincia.id +'" class="item" '+ (provincia.id == _data.provincia.value ? 'selected' : '') +'>'+ provincia.nombre +'</option>');
                }
				$(_data.provincia.field).trigger('change');
            }, 'json');
        });
        
        $(_data.departamento.field).trigger('change');
    },
    filterCountryDPD: function(_data){
        _data = $.extend({
            pais: {
                field: '#pais_id'
            },
            departamento: {
                field: '#departamento_id',
                value: ''
            },
            provincia: {
                field: '#provincia_id',
                value: ''
            },
            distrito: {
                field: '#distrito_id',
                value: ''
            },
        }, _data);
        
        // PROVINCIA
        $(_data.provincia.field).bind('change', function(){
            $(_data.distrito.field).find('.item').remove();
            $.post('/info/get_distritos', {provincia_id: $(_data.provincia.field).val()}, function(r){
                for(i in r){
                    distrito = r[i];
                    $(_data.distrito.field).append('<option value="'+ distrito.id +'" class="item" '+ (distrito.id == _data.distrito.value ? 'selected' : '') +'>'+ distrito.nombre +'</option>');
                }
                $(_data.distrito.field).trigger('change');
            }, 'json');
        });
        // DEPARTAMENTO
        $(_data.departamento.field).bind('change', function(){
            $(_data.provincia.field).find('.item').remove();
            $.post('/info/get_provincias', {departamento_id: $(_data.departamento.field).val()}, function(r){
                for(i in r){
                    provincia = r[i];
                    $(_data.provincia.field).append('<option value="'+ provincia.id +'" class="item" '+ (provincia.id == _data.provincia.value ? 'selected' : '') +'>'+ provincia.nombre +'</option>');
                }
                $(_data.provincia.field).trigger('change');
            }, 'json');
        });
        // PAIS HANDLER
        $(_data.pais.field).bind('change', function(){
            $(_data.departamento.field).find('.item').remove();
            $.post('/info/get_departamentos', {pais_id: $(_data.pais.field).val()}, function(r){
                
                for(i in r){
                    departamento = r[i];
                    $(_data.departamento.field).append('<option value="'+ departamento.id +'" class="item" '+ (departamento.id == _data.departamento.value ? 'selected' : '') +'>'+ departamento.nombre +'</option>');
                }
                $(_data.departamento.field).trigger('change');
            }, 'json');
        });
        
        $(_data.pais.field).trigger('change');
    }
};

function fancybox(url, addHistory){
    if(addHistory !== false){
        zk.fancyboxHistory.push(url);
        zk.fancyboxIndex = zk.fancyboxHistory.length - 1;
        //++zk.fancyboxIndex;
    }

    $.fancybox({
        href: url, 
        type: 'ajax', 
        enableEscapeButton: false,  
        hideOnOverlayClick: false, 
        overlayColor : '#FFF', 
        centerOnScroll: true, 
        scrolling: 'no',
        //autoHeight : true,
        closeClick: false, 
        helpers: {
            overlay : {
                closeClick : false,
                background: '#fff'
            }
        }
    });
}

function setMenuActive(id){
	$('#mainnav-menu li').removeClass('active').removeClass('active-sub');
	$('#menu-' + id).addClass('active-sub')
}

$.fn.extend({
    sendGateway: function(action){
        _form = this;
        _form.niftyOverlay('show');
        if(!action){
             action = this.attr('action');
             this.attr('action', '');
        }
        $('#ActionManager').remove();
        $('body').append('<iframe id="ActionManager" name="ActionManager" style="display: none"></iframe>');
        this.attr('method', 'POST');
        this.attr('target', 'ActionManager');
        this.attr('enctype','multipart/form-data');
        this.attr('action', action);
        form = document.getElementById(this.attr('id'));
        iframe = document.getElementById('ActionManager');
        form.submit();
    },
    sendForm: function(url, callback){
        _form = this;
       
        var formData = new FormData(_form[0]);

        $.ajax({
            url: url,
            type: 'POST',
            beforeSend: function(){
                _form.niftyOverlay('show');
            },
            processData: false,
            contentType: false,
            data: formData,
            dataType: 'json',
            success: function(r){
                if(callback) return callback(r);
            },
            complete: function(){
                _form.niftyOverlay('hide');
            },
            error: function(){
                alert('No se pudo realizar la petición');
            }
        });
    },
    changeGradoOptions: function(data){
        data = $.extend({value: '', showLabel: true, label: 'Seleccione'}, data);
        let _form = this;
        let _nivel = $('[name="nivel_id"]', $(_form));
    
        _nivel.bind('change', function(){
            _grado = $('[name="grado"]', $(_form));
            console.log(_form)
             
            if(this.value == '' || this.value == null) return false;
            _grado.find('option').remove();
            _data = NIVELES[this.value];
            if(data.showLabel !== false) _grado.append('<option value="">-- '+ data.label +' --</option>');
            for(i=_data.grado_minimo;i<=_data.grado_maximo;i++){
                _grado.append('<option value="'+ i +'" '+ (i == data.value ? 'selected' : '') +'>'+ i +''+ (_data.definicion_grado == 1 ? ' Años' : 'º Grado') +'</option>');
            }
            if(_data.avanzada == 'SI'){
                _grado.append('<option value="-1" '+ (-1 == data.value ? 'selected' : '') +'>Avanzada</option>');
            }
        });
        
        _nivel.trigger('change');
    }
});

/* Set the defaults for DataTables initialisation */
if($.fn.dataTable)
    $.extend(true, $.fn.dataTable.defaults, {
        "iDisplayLength": 100,
        "pageLength": 100,
        "sDom": "<'row'<'col-sm-6'l><'col-sm-6'f>r>" + "t" + "<'row'<'col-sm-6'i><'col-sm-6'p>>",
        //"responsive": true,
        "oLanguage": {
            "sLengthMenu": "Mostrar _MENU_ registros por página",
            //"sLengthMenu": "Mostrar _MENU_ por página",
            "sZeroRecords": "No se encontró ningun registro",
            "sInfo": "Mostrando _START_ - _END_ de _TOTAL_",
            "sInfoEmpty": "Mostrando 0 - 0 de 0",
            "sEmptyTable": "No hay datos disponibles",
            "sInfoFiltered": "(filtrado de _MAX_ en total)",
            "sSearch":"Filtrar:",
            "oPaginate": {
                "sFirst":    "Primero",
                //"sPrevious": '<i class="fa fa-angle-left"></i>',
				//"sNext":     '<i class="fa fa-angle-right"></i>',
				"sPrevious": '<i class="demo-psi-arrow-left"></i>',
              	"sNext": '<i class="demo-psi-arrow-right"></i>',
                "sLast":     "Último"
            }
        },
    });

$(window).on('keydown', function(e){
    if(e.keyCode == 121){
        zk.reloadPage();
    }
    if(e.keyCode == 120){
        $.fancybox.reload();
    }
    //console.log(e.keyCode);
})


function formatBytes(bytes,decimals) {
   if(bytes == 0) return '0 Byte';
   var k = 1000; // or 1024 for binary
   var dm = decimals + 1 || 3;
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function selectChangeRoleWindow(r){
    var message = '<div class="form-block">';
    for(i in r.roles){
        message += '<button class="btn btn-block btn-primary" onclick="doChangeRole(\''+ r.roles[i] +'\', \''+ r.token +'\')">'+ r.roles[i] +'</button>';
    }
    message += '</div>';

    bootbox.dialog({
        title: "Seleccione un rol",
        message: message,
        size: 'small'
    });
}

function doChangeRole(role, token){
    $.post('/usuarios/change_role', {role: role, token: token}, function(r){
        if(parseInt(r[0]) == 1){
            window.location = '/';

        }else{
            $.prompt('Operación fallida');
        }
    }, 'json');
}

/** UTILS **/

class PublicacionesApp{
    constructor(container){
        this.endpoint = "/publicaciones/lista";
        this.container = container;
        $(this.container).niftyOverlay();
    }

    loadPage(page, sender){
        $(this.container).niftyOverlay('show');
        let _this = this;
        $.get(this.endpoint, {page: page}, function(r){
            $(_this.container).niftyOverlay('hide');
            if(page > 1){
                $(_this.container).append(r);
            }else{
                $(_this.container).html(r);
            }
            if(sender){
                $(sender).remove()
            }
        });
    }
}

class DocumentProvider{
	constructor(scope, form){
		this.scope = scope;
		this.form = $(form);
		this.numeroDocumentoField = $('#' + this.scope + '_numero_documento');
		this.tipoField = $('#' + this.scope + '_tipo_persona_id');
	}

	selectDocumentType(value){
		value = value.trim()
		
		if(value.startsWith('20')){
			$('#' + this.scope + '_tipo_documento_id').val(2);
			this.tipoField.val(2);
		}
		if(value.startsWith('10')){
			$('#' + this.scope + '_tipo_documento_id').val(2);
			this.tipoField.val(1);
		}

		if(value.length == 8){
			$('#' + this.scope + '_tipo_documento_id').val(1);
			this.tipoField.val(1);
		}

		this.tipoField.trigger('change')
	}

	getDNI(value){
		let _this = this;
		_this.form.niftyOverlay('show');
		$.get('/info/dni/'+ value, null, function(r){
			_this.form.niftyOverlay('hide');
			if(r.nombres == ""){
				return zk.pageAlert({message: 'El número de documento es inválido.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}
			$('#' + _this.scope + '_nombres').val(r.nombres);
			$('#' + _this.scope + '_apellidos').val(r.apellidoPaterno + ' ' + r.apellidoMaterno);

		}, 'json').error(function(){
			zk.pageAlert({message: 'No se pudo verificar el número de documento.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			$('#' + _this.scope + '_nombres').val('');
			$('#' + _this.scope + '_apellidos').val('');
			_this.form.niftyOverlay('hide');
		});
	}

	getRUC(value){
		let _this = this;
		_this.form.niftyOverlay('show');
		$.get('/info/ruc/'+ value, null, function(r){
			_this.form.niftyOverlay('hide');
			if(r.nombres == ""){
				return zk.pageAlert({message: 'El número de documento es inválido.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			}
			$('#' + _this.scope + '_razon_social').val(r.nombre);
			$('#' + _this.scope + '_nombre_comercial').val(r.nombre);
			$('#' + _this.scope + '_direccion').val(r.direccion);

		}, 'json').error(function(){
			zk.pageAlert({message: 'No se pudo verificar el número de documento.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
			$('#' + _this.scope + '_razon_social').val('');
			$('#' + _this.scope + '_nombre_comercial').val('');
			_this.form.niftyOverlay('hide');
		});
	}

	getData(value){
		value = value.trim()

		if(value.length == 8){
			return this.getDNI(value);
		}

		if(value.length == 11){
			if(value.startsWith('10')){
				let dni = value.substring(2, 10);
				return this.getDNI(dni);
			}

			if(value.startsWith('20')){
				return this.getRUC(value);
			}
		}

		zk.pageAlert({message: 'El número de documento es inválido.', title: 'Operación Fallida', icon: 'bolt', type: 'danger', container: 'floating'});
	}

	check(){
		if(this.numeroDocumentoField.val() != ""){
			this.getData(this.numeroDocumentoField.val());
		}
	}

	setup(){
		let _this = this;
	

		this.numeroDocumentoField.on('blur', function(){
			if(this.value != ""){
				_this.selectDocumentType(this.value);
				//_this.getData(this.value);
			}
		});

		$('#documentProviderCheck').on('click', function(){
			_this.check();
		})
	}
}

function  ckEditorSimple(selector, editorVar){
    if(!editorVar)
        editorVar = "editor";

    ClassicEditor
            .create( document.querySelector( selector ), {
                
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'outdent',
                        'indent',
                        '|',
                        'alignment',
                        'imageInsert',
                    
                        'blockQuote',
                        'insertTable',
                        'mediaEmbed',
                        'undo',
                        'redo',
                        
                        'fontBackgroundColor',
                        'fontColor',
                        'fontSize',
                        'fontFamily',
                        'highlight',
                        'horizontalLine',
                    
                        'removeFormat',
                        
                        
                        'underline',
                        
                    ],
                    shouldNotGroupWhenFull: true
                },
                language: 'es',
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'imageStyle:full',
                        'linkImage',
                        //'imageStyle:side',
                    
                        'imageStyle:alignLeft',
                        'imageStyle:alignCenter',
                        'imageStyle:alignRight',
                        
                    ],
                    styles: [
                        // This option is equal to a situation where no style is applied.
                        'full',

                        'alignCenter',
                        // This represents an image aligned to the left.
                        'alignLeft',

                        // This represents an image aligned to the right.
                        'alignRight'
                    ],
                    resizeUnit: 'px',
                },
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells',
                        'tableCellProperties',
                        'tableProperties'
                    ]
                },
                licenseKey: '',
                simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: '/home/upload_image',

                    // Enable the XMLHttpRequest.withCredentials property.
                    withCredentials: true,

                    // Headers sent along with the XMLHttpRequest to the upload server.
                    headers: {
                        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content"),
                        //Authorization: 'Bearer <JSON Web Token>'
                    }
                }
                
            } )
            .then( editor => {
                window[editorVar] = editor;
            } )
            .catch( error => {
                console.error( 'Oops, something went wrong!' );
                console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
                console.warn( 'Build id: wrkpprsd1v44-c3v5ops1ycd' );
                console.error( error );
            } );
}


function  ckEditorVerySimple(selector, editorVar){
    if(!editorVar)
        editorVar = "editor";

    ClassicEditor
            .create( document.querySelector( selector ), {
                
                toolbar: {
                    items: [
                        
                        'bold',
                        'italic',
           
                        'alignment',
                        'imageInsert',
                        'fontBackgroundColor',
                        'fontColor',
                        'fontSize',
                        'fontFamily',
                        'highlight',
                
                        
                    ],
                    shouldNotGroupWhenFull: true
                },
                language: 'es',
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'imageStyle:full',
                        'linkImage',
                        //'imageStyle:side',
                    
                        'imageStyle:alignLeft',
                        'imageStyle:alignCenter',
                        'imageStyle:alignRight',
                        
                    ],
                    styles: [
                        // This option is equal to a situation where no style is applied.
                        'full',

                        'alignCenter',
                        // This represents an image aligned to the left.
                        'alignLeft',

                        // This represents an image aligned to the right.
                        'alignRight'
                    ],
                    resizeUnit: 'px',
                },
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells',
                        'tableCellProperties',
                        'tableProperties'
                    ]
                },
                licenseKey: '',
                simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: '/home/upload_image',

                    // Enable the XMLHttpRequest.withCredentials property.
                    withCredentials: true,

                    // Headers sent along with the XMLHttpRequest to the upload server.
                    headers: {
                        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content"),
                        //Authorization: 'Bearer <JSON Web Token>'
                    }
                }
                
            } )
            .then( editor => {
                window[editorVar] = editor;
            } )
            .catch( error => {
                console.error( 'Oops, something went wrong!' );
                console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
                console.warn( 'Build id: wrkpprsd1v44-c3v5ops1ycd' );
                console.error( error );
            } );
}