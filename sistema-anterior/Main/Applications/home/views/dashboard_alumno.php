
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
					<li class="active">Alumno</li>
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
			                        <span class="text-3x text-thin">{{ totalAsignaturas }}</span>
			                        <p>Cursos Asignados</p>
			                        <i class="fa fa-user icon-lg"></i>
			                    </div>
			                </div>
						</div>
						<div class="col-lg-4">
							<div class="panel panel-purple panel-colorful">
			                    <div class="pad-all text-center">
			                        <span class="text-3x text-thin">{{ totalAsignaturas }}</span>
			                        <p>Docentes</p>
			                        <i class="fa fa-user icon-lg"></i>
			                    </div>
			                </div>
						</div>
						<div class="col-lg-4">
							<div class="panel panel-info panel-colorful">
			                    <div class="pad-all text-center">
			                        <span class="text-3x text-thin">{{ totalMatriculas }}</span>
			                        <p>Matrículas</p>
			                        <i class="demo-psi-receipt-4 icon-lg"></i>
			                    </div>
			                </div>
						</div>
					</div>
					
					
					<div class="row">
						<div class="col-lg-12">
							<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Próximos Exámenes</h3>
								</div>
								<div class="panel-body">
									{% if examenes|length > 0 %}
									<table class="special">
										<tr>
					                		<th>Examen</th>
					                       	<th>Curso</th>
					                        <th>Fecha</th>
					                		<th></th>
					                	</tr>
					                	{% for examen in examenes %}
					                	<tr>
					                		<td>{{ examen.titulo }}</td>
					                        <td>{{ examen.asignatura.curso.nombre }}</td>
					                        <td class="text-center"><span class="tip" title="El examen se activará a la fecha/hora indicada">{{ parseFecha(examen.fecha_desde) }} - {{ examen.hora_desde|date('h:i A') }}</span></td>
					                        <td class="text-center"><button class="btn btn-primary" onclick="zk.goToUrl('/aula_virtual/index/{{ sha1(examen.asignatura_id) }}')">Ver Más</button></td>
					                	</tr>
					                	{% endfor %}
									</table>	
									{% else %}
                                    <p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
									{% endif %}
								</div>
							</div>
						</div>
					</div>
					

					<div class="row">
						<div class="col-lg-12">
							<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Próximas Tareas</h3>
								</div>
								<div class="panel-body">
									{% if tareas|length > 0 %}
									<table class="special">
										<tr>
					                		<th>Nombre</th>
					                		<th>Curso</th>
					                        <th style="width: 150px">Fecha de Registro</th>
					                        <th style="width: 150px">Fecha de Entrega</th>
					                		<th></th>
					                	</tr>
					                	{% for tarea in tareas %}
					                	<tr>
					                		<td>{{ tarea.titulo }}</td>
					                		<td>{{ tarea.asignatura.curso.nombre }}</td>
					                        <td class="text-center">{{ tarea.fecha_hora|date('d-m-Y') }}</td>
					                        <td class="text-center">{{ tarea.fecha_entrega|date('d-m-Y') }}</td>
					                        <td class="text-center"><button class="btn btn-primary" onclick="zk.goToUrl('/asignaturas_tareas/detalles/{{ sha1(tarea.id) }}')">Ver Más</button></td>
					                	</tr>
					                	{% endfor %}
									</table>	
									{% else %}
                                    <p class="text-center">NO SE ENCONTRARON RESULTADOS</p>
									{% endif %}
								</div>
							</div>
						</div>
					</div>
                </div>
                <!--===================================================-->
                <!--End page content-->