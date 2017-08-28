/**
 * Created by dominguez on 3/05/2017.
 */

/**
 * [dataTables Funcion para cargar datos en la tablas de los reportes]
 * @param  {String} nombreDIV [Nombre del div donde esta la tabla para agregar los datos]
 * @param  {String} data      [Nombre del tipo de porte a cargar]
 * @param  {String} route     [Ruta a la cual va a consultar los datos a cargar]
 */
const dataTables = (nombreDIV, data, route) => {
  //Eliminación del DataTable en caso de que exista
  $('#' + nombreDIV).dataTable().fnDestroy()
  //Creacion del DataTable
  $('#' + nombreDIV).DataTable({
    'deferRender': true,
    'responsive': false,
    'processing': true,
    'serverSide': true,
    'ajax': {
      url: route,
      type: 'POST',
      data: data
    },
    'order': [
      [1, 'asc']
    ],
    'paging': true,
    'pageLength': 100,
    'lengthMenu': [100, 200, 300, 400, 500],
    'scrollY': '300px',
    'scrollX': true,
    'scrollCollapse': true,
    'select': true,
    'language': dataTables_lang_spanish(),
    'columns': columnsDatatable(route)
  })
}

/**
 * [dataTables_lang_spanish Función que permite colocar el Datable en español]
 */
const dataTables_lang_spanish = () => {
  let lang = {
    'sProcessing': 'Procesando...',
    'sLengthMenu': 'Mostrar _MENU_ registros',
    'sZeroRecords': 'No se encontraron resultados',
    'sEmptyTable': 'Ningún dato disponible en esta tabla',
    'sInfo': 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
    'sInfoEmpty': 'Mostrando registros del 0 al 0 de un total de 0 registros',
    'sInfoFiltered': '(filtrado de un total de _MAX_ registros)',
    'sInfoPostFix': '',
    'sSearch': 'Buscar:',
    'sUrl': '',
    'sInfoThousands': ',',
    'sLoadingRecords': 'Cargando...',
    'oPaginate': {
      'sFirst': 'Primero',
      'sLast': 'Último',
      'sNext': 'Siguiente',
      'sPrevious': 'Anterior'
    },
    'oAria': {
      'sSortAscending': ': Activar para ordenar la columna de manera ascendente',
      'sSortDescending': ': Activar para ordenar la columna de manera descendente'
    }
  }

  return lang
}

/**
 * Created by dominguez on 10/03/2017.
 *
 * [columns_datatable description]
 * @param  {String} route [Nombre del tipo de reporte]
 * @return {Array}        [Array con nombre de cada parametro que ira en las columnas de la tabla dl reporte]
 */
const columnsDatatable = (route) => {
  let columns = ''
    if (route === 'incoming_calls') {
      columns = [
        {'data': 'date'},
        {'data': 'hour'},
        {'data': 'telephone'},
        {'data': 'agent'},
        {'data': 'skill'},
        {'data': 'duration'},
        {'data': 'action'},
        {'data': 'waittime'},
        {'data': 'download'},
        {'data': 'listen'}
      ]
    }

    if (route === 'surveys') {
      columns = [
        {'data': 'Type Survey'},
        {'data': 'Date'},
        {'data': 'Hour'},
        {'data': 'Username'},
        {'data': 'Anexo'},
        {'data': 'Telephone'},
        {'data': 'Skill'},
        {'data': 'Duration'},
        {'data': 'Question_01'},
        {'data': 'Answer_01'},
        {'data': 'Question_02'},
        {'data': 'Answer_02'},
        {'data': 'Action'}
      ]
    }

    if (route === 'consolidated_calls') {
      columns = [
        {'data': 'Name'},
        {'data': 'Received'},
        {'data': 'Answered'},
        {'data': 'Abandoned'},
        {'data': 'Transferred'},
        {'data': 'Attended'},
        {'data': 'Answ 10s'},
        {'data': 'Answ 15s'},
        {'data': 'Answ 20s'},
        {'data': 'Answ 30s'},
        {'data': 'Aband 10s'},
        {'data': 'Aband 15s'},
        {'data': 'Aband 20s'},
        {'data': 'Aband 30s'},
        {'data': 'Wait Time'},
        {'data': 'Talk Time'},
        {'data': 'Avg Wait'},
        {'data': 'Avg Talk'},
        {'data': 'Answ'},
        {'data': 'Unansw'},
        {'data': 'Ro10'},
        {'data': 'Ro15'},
        {'data': 'Ro20'},
        {'data': 'Ro30'},
        {'data': 'Ns10'},
        {'data': 'Ns15'},
        {'data': 'Ns20'},
        {'data': 'Ns30'},
        {'data': 'Avh2 10'},
        {'data': 'Avh2 15'},
        {'data': 'Avh2 20'},
        {'data': 'Avh2 30'}
      ]
    }

    if (route === 'events_detail') {
      columns = [
        {'data': 'nombre_agente'},
        {'data': 'fecha'},
        {'data': 'hora'},
        {'data': 'evento'},
        {'data': 'accion'}
      ]
    }

    if (route === 'outgoing_calls') {
      columns = [
        {'data': 'date'},
        {'data': 'hour'},
        {'data': 'annexedorigin'},
        {'data': 'username'},
        {'data': 'destination'},
        {'data': 'calltime'},
        {'data': 'download'},
        {'data': 'listen'}
      ]
    }

    if (route === 'agents_online') {
        columns = [
            {"data":"date"},
            {"data":"hour"},
            {"data":"agents"}
        ]
    }

    if (route === 'level_of_occupation') {
      columns = [
        {"data":"date"},
        {"data":"hour"},
        {"data":"indbound"},
        {"data":"acw"},
        {"data":"outbound"},
        {"data":"auxiliares"},
        {"data":"logueo"},
        {"data":"occupation_cosapi"}
      ]
    }

    if (route === 'list_users') {
      columns = [
        {"data":"Id"},
        {"data":"First Name"},
        {"data":"Second Name"},
        {"data":"Last Name"},
        {"data":"Second Last Name"},
        {"data":"Username"},
        {"data":"Role"},
        {"data":"Estado"},
        {"data":"Change Rol"},
        {"data":"Change Password"},
        {"data":"Change Status"}
      ]
    }

    return columns
}

/**
 * [DatableHide description]
 * @param  {String} nombreDiv [Nombre del id de la tabla]
 * @param {Array}  numeroColumnas[Se pasan los numeros de columnas que se desean ocultar]
 * @return Oculta las columnas en el datatable
 */
const DataTableHide = (nombreDIV, numeroColumnas, roleUser) => {
  let exist = RoleTableHide().indexOf(roleUser)
  if(exist >= 0) {
    let DataTableDiv = $(`#${nombreDIV}`).DataTable()
    DataTableDiv.columns( numeroColumnas ).visible( false, false )
    DataTableDiv.columns.adjust().draw( false )
  }
}