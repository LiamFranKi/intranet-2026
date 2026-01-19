{% extends main_template %}
{% block main_content %}
<script>
    $(function() {
        $('#listaInvoicePayments').dataTable();
        setMenuActive('invoicePayments');
    });

    function borrarInvoicePayment(id) {
        zk.confirm('¿Está seguro de borrar los datos?', function() {
            zk.sendData('/invoice_payments/borrar', {
                id: id
            }, function(r) {
                if (parseInt(r[0]) == 1) {
                    zk.pageAlert({
                        message: 'Datos borrados correctamente',
                        title: 'Operación Exitosa',
                        icon: 'check',
                        type: 'success',
                        container: 'floating'
                    });
                    $.fancybox.reload()
                } else {
                    zk.pageAlert({
                        message: 'No se pudieron borrar los datos',
                        title: 'Operación Fallida',
                        icon: 'remove',
                        type: 'danger',
                        container: 'floating'
                    });
                }
            });
        });
    }
</script>

<div class="modal-content modal-800">
    <div class="modal-header">
        <h3 class="modal-title">Pagos Registrados</h3>
    </div>
    <div class="modal-body">

        {% if invoice.getTotalPaymentsRemain() > 0 %}
        <div class="pad-btm form-inline">
            <div class="row">
                <div class="col-sm-6 table-toolbar-left">
                    <a href="javascript:;" onclick="fancybox('/invoice_payments/form?invoice_id={{ params.invoice_id }}')" class="btn btn-success btn-labeled fa fa-plus">Registrar Nuevo</a>
                </div>
            </div>
        </div>
        {% endif %}

        <table class="special" style="width: 100%; text-align: center">
            <tbody>
                <tr>
                    <th>Monto a pagar </th>
                    <td>{{ invoice.getMontoTotal()|number_format(2) }}</td>
                    <th>Monto pagado </th>
                    <td>{{ invoice.getTotalPayments()|number_format(2) }}</td>
                    <th>Monto que adeuda </th>
                    <td>{{ invoice.getTotalPaymentsRemain()|number_format(2) }}</td>
                </tr>
            </tbody>
        </table>

        <table id="listaInvoicePayments" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Comentarios</th>
                    <th>Monto S/</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {% for invoicePayment in invoicePayments %}
                <tr>
                    <td class="text-center">{{ invoicePayment.created_at|date('Y-m-d h:i A') }}</td>
                    <td>{{ invoicePayment.comments }}</td>
                    <td class="text-center">{{ invoicePayment.amount|number_format(2) }}</td>

                    <td class="text-center" style="width: 120px">
                        <div class="btn-group dropup">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Opciones <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="javascript:;" onclick="fancybox('/invoice_payments/form/{{ sha1(invoicePayment.id) }}')">{{ icon('register') }} Editar Pago</a></li>
                                <li><a href="javascript:;" onclick="borrarInvoicePayment('{{ sha1(invoicePayment.id) }}')">{{ icon('delete') }} Borrar Pago</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>


{% endblock %}