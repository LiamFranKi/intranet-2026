<script>
$(function(){
    setMenuActive('dashboard');
    var d1 = [
    	{% for data in dataTotalMatriculas %}
    		[{{ loop.index - 1 }}, {{ data.total }}],
    	{% endfor %}
    	],
        d2 = [
    	{% for data in dataTotalMatriculas %}
    		[{{ loop.index - 1 }}, {{ data.hombres }}],
    	{% endfor %}
    	],
        d3 = [
    	{% for data in dataTotalMatriculas %}
    		[{{ loop.index - 1 }}, {{ data.mujeres }}],
    	{% endfor %}
    	];


    try{


        $.plot("#bar-chart", [
            {
                label: "Total",
                data: d1
            },{
                label: "Hombres",
                data: d2
            },{
                label: "Mujeres",
                data: d3
            }],{
            series: {
                bars: {
                    show: true,
                    lineWidth: 0,
                    barWidth: 0.25,
                    align: "center",
                    order: 1,
                    fillColor: { colors: [ { opacity: .9 }, { opacity: .9 } ] }
                }
            },
            colors: ['#03a9f4', '#ffb300', '#e3e8ee'],
            grid: {
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            yaxis: {
                ticks: 4, tickColor: 'rgba(0,0,0,.02)'
            },
            xaxis: {
                ticks: [
                {% for data in dataTotalMatriculas %}
                [{{ loop.index - 1 }}, "{{ _key }}"],
                {% endfor %}
                ],
                tickColor: 'transparent'
            },
            tooltip: {
                show: true,
                content: "<div class='flot-tooltip text-center'><h5 class='text-main'>%s</h5>%y.0 </div>"
            }
        });



        $.plot("#bar-chart-ingresos-anuales", [
            {
                label: "Total",
                data: [
                {% for data in dataIngresosAnuales %}
                [{{ loop.index - 1 }}, {{ data }}],
                {% endfor %}
                ]
            }],{
            series: {
                bars: {
                    show: true,
                    lineWidth: 0,
                    barWidth: 0.25,
                    align: "center",
                    order: 1,
                    fillColor: { colors: [ { opacity: .9 }, { opacity: .9 } ] }
                }
            },
            colors: ['#03a9f4', '#ffb300', '#e3e8ee'],
            grid: {
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            yaxis: {
                ticks: 4, tickColor: 'rgba(0,0,0,.02)'
            },
            xaxis: {
                ticks: [
                {% for data in dataIngresosAnuales %}
                [{{ loop.index - 1 }}, "{{ _key }}"],
                {% endfor %}
                ],
                tickColor: 'transparent'
            },
            tooltip: {
                show: true,
                content: "<div class='flot-tooltip text-center'><h5 class='text-main'>%s</h5>%y.0 </div>"
            }
        });


        $.plot("#bar-chart-alumnos-grado", [
            {% for nivel in niveles %}
            {
                label: "{{ nivel.nombre }}",
                data: [
                    {% for i in 1..6 %}
                    [{{ loop.index - 1 }}, {{ round(dataTotalAlumnosGrado[nivel.id][i], 0) }}], 
                    {% endfor %}
                    //[1, 2], [2, 3], [3, 4], [4, 5], [5, 6]
                ]
            },
            {% endfor %}
            
            ],{
            series: {
                bars: {
                    show: true,
                    lineWidth: 0,
                    barWidth: 0.25,
                    align: "center",
                    order: 1,
                    fillColor: { colors: [ { opacity: .9 }, { opacity: .9 } ] }
                }
            },
            colors: ['#03a9f4', '#ffb300', '#e3e8ee'],
            grid: {
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            yaxis: {
                ticks: 4, tickColor: 'rgba(0,0,0,.02)'
            },
            xaxis: {
                ticks: [
                {% for i in 1..6 %}
                [{{ loop.index - 1 }}, "{{ i }}°"],
                {% endfor %}
                ],
                tickColor: 'transparent'
            },
            tooltip: {
                show: true,
                content: "<div class='flot-tooltip text-center'><h5 class='text-main'>%s</h5>%y.0 </div>"
            }
        });


        $.plot("#bar-chart-ingresos-deudas-mes", [
                                            
            {
                label: "CANCELADO",
                data: [
                    {% for i in 1..COLEGIO.total_pensiones %}   
                        [{{ i - 1 }}, {{ round(dataIngresosDeudasMes[i]['CANCELADO'], 2) }}],
                    {% endfor %}
                ]
            },

            {
                label: "DEUDA",
                data: [
                    {% for i in 1..COLEGIO.total_pensiones %}   
                        [{{ i - 1 }}, {{ round(dataIngresosDeudasMes[i]['PENDIENTE'], 2) }}],
                    {% endfor %}
                ]
            },
        
        
            ],{
            series: {
                bars: {
                    show: true,
                    lineWidth: 0,
                    barWidth: 0.25,
                    align: "center",
                    order: 1,
                    fillColor: { colors: [ { opacity: .9 }, { opacity: .9 } ] }
                }
            },
            colors: ['#03a9f4', '#ffb300', '#e3e8ee'],
            grid: {
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            yaxis: {
                ticks: 4, tickColor: 'rgba(0,0,0,.02)'
            },
            xaxis: {
                ticks: [
                {% for mes in COLEGIO.MESES %}
                   {% if loop.index > 2 %}
                        [{{ loop.index - 3 }}, "{{ mes }}"],
                   {% endif %}
                    
                {% endfor %}
                ],
                tickColor: 'transparent'
            },
            tooltip: {
                show: true,
                content: "<div class='flot-tooltip text-center'><h5 class='text-main'>%s</h5>%y.0 </div>"
            }
        });
    }catch(e){
        
    }
})
</script>
                <div id="page-head">
                    
                    <!--Page Title-->
                    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                    <div id="page-title">
                        <h1 class="page-header text-overflow">Dashboard</h1>
                    </div>
                    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                    <!--End page title-->


                    <!--Breadcrumb-->
                    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                    <ol class="breadcrumb">
					<li><a href="#"><i class="demo-pli-home"></i></a></li>
					<li class="active">Dashboard 2</li>
                    </ol>
                    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
                    <!--End breadcrumb-->

                </div>

              

                <!--Page content-->
                <!--===================================================-->
                <div id="page-content">
                    
					
					<!--Activated Users Chart-->
					<!--===================================================-->
					
					<!--===================================================-->
					<!--End Activated Users chart-->
					<div class="row">
						<div class="col-lg-4">
							<div class="panel panel-primary panel-colorful">
			                    <div class="pad-all text-center">
			                        <span class="text-3x text-thin">{{ totalHombres + totalMujeres }}</span>
			                        <p>Matrículas</p>
			                        <i class="fa fa-user icon-lg"></i>
			                    </div>
			                </div>
						</div>
						<div class="col-lg-4">
							<div class="panel panel-purple panel-colorful">
			                    <div class="pad-all text-center">
			                        <span class="text-3x text-thin">{{ totalTrabajadores }}</span>
			                        <p>Trabajadores</p>
			                        <i class="fa fa-user icon-lg"></i>
			                    </div>
			                </div>
						</div>
						<div class="col-lg-4">
							<div class="panel panel-info panel-colorful">
			                    <div class="pad-all text-center">
			                        <span class="text-3x text-thin">{{ totalGrupos }}</span>
			                        <p>Grupos</p>
			                        <i class="demo-psi-receipt-4 icon-lg"></i>
			                    </div>
			                </div>
						</div>
					</div>
					
					<div class="row">
					    
						
						<div class="col-lg-12">
							<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Matrículas por Año</h3>
								</div>
							    <!--Chart information-->
							    <div class="panel-body">
									<div id="bar-chart" style="height:250px"></div>
					                
					                <hr class="new-section-xs bord-no">
					                <ul class="list-inline text-center">
					                    <li><span class="label label-info">{{ totalHombres + totalMujeres }}</span> Total</li>
					                    <li><span class="label label-warning">{{ totalHombres }}</span> Hombres</li>
					                    <li><span class="label label-default">{{ totalMujeres }}</span> Mujeres</li>
					                </ul>
					            	
							    </div>
							</div>
						</div>	
					</div>

					<div class="row">
					    
						
						<div class="col-lg-12">
							<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Ingresos Anuales</h3>
								</div>
							    <!--Chart information-->
							    <div class="panel-body">
									<div id="bar-chart-ingresos-anuales" style="height:250px"></div>
					            	
							    </div>
							</div>
						</div>	
					</div>
					
					<div class="row">
                        
                        
                        <div class="col-lg-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Alumnos por Grado - {{ COLEGIO.anio_activo }}</h3>
                                </div>
                                <!--Chart information-->
                                <div class="panel-body">
                                          
                                    <div id="bar-chart-alumnos-grado" style="height:250px"></div>
                                    
                                </div>
                            </div>
                        </div>  
                    </div>



                    <div class="row">
                        
                        
                        <div class="col-lg-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Ingresos / Deudas x Mes - {{ COLEGIO.anio_activo }}</h3>
                                </div>
                                <!--Chart information-->
                                <div class="panel-body">
                                          
                                    <div id="bar-chart-ingresos-deudas-mes" style="height:250px"></div>
                                    
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
                <!--===================================================-->
                <!--End page content-->