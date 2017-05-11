/**
 * Created by dominguez on 3/05/2017.
 */

var AdminLTEOptions = {
  sidebarExpandOnHover: true,
  enableBSToppltip: true
}

$(document).ready(function () {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  })

  $('#statusAgent').click(function () { PanelStatus() })
  $('.reportes').on('click', function (e) { loadModule($(this).attr('id')) })
})

/*[show_tab_incoming Función que carga Llamadas Entrantes en el reporte] */
const show_tab_incoming = (evento) => dataTables('table-incoming', get_data_filters(evento), 'incoming_calls')

/*[show_tab_surveys Función que carga los datos de las Encuenstas] */
const show_tab_surveys = (evento) => dataTables('table-surveys', get_data_filters(evento), 'surveys')

/*[show_tab_consolidated Función que carga los datos del Consolidado]*/
const show_tab_consolidated = (evento) => dataTables('table-consolidated', get_data_filters(evento), 'consolidated_calls')

/*[show_tab_detail_events Función que carga los datos detallados de los Eventos del Agente]*/
const show_tab_detail_events = (evento) => dataTables('table-detail-events', get_data_filters(evento), 'events_detail')

/*[show_tab_detail_events Función que carga los datos detallados de los Eventos del Agente]*/
const show_tab_level_occupation = (evento) => dataTables('table-level-occupation', get_data_filters(evento), 'level_of_occupation')

/*[show_tab_angetOnline Función que carga los datos de los agentes online]*/
const show_tab_agentOnline = (evento) => dataTables('table-agentOnline', get_data_filters(evento), 'agents_online')

/*[show_tab_outgoing Función que carga los datos de las Llamadas Salientes]*/
const show_tab_outgoing = (evento) => dataTables('table-outgoing', get_data_filters(evento), 'outgoing_calls')

/*[show_tab_list_user Función que carga los datos detallados de los usuarios]*/
const show_tab_list_user = (evento) => dataTables('table-list-user', get_data_filters(evento), 'list_users')

/*[show_tab_annexed Función que carga la lista de anexos]*/
const show_tab_annexed = (event) => {
  let token = $('input[name=_token]').val()
  let imageLoading = `<div class="loading" id="loading"><li></li><li></li><li></li><li></li><li></li></div>`
  $.ajax({
    type: 'POST',
    url: 'agents_annexed/list_annexed',
    cache: false,
    data: {
      _token : token,
      event : event
    },
    beforeSend : () => {
      $('#divListAnnexed').html(imageLoading)
    },
    success: (data) =>{
      $('#divListAnnexed').html(data)
    }
  })
}

const activeCalls = () => {
  vueFront.remoteActiveCallsUserId = vueFront.getUserId
  let message = '<h4>¿Usted desea poder recibir llamadas?</h4>' +
    '<br>' +
    '<p><b>Nota : </b>Cuando active la entrada de llamadas pasara a un estado de "Login", porfavor de leer las ' +
    'notificaiones para saber que el cambio se realizo exitosamente y verificar que su rol este en "User", para luego' +
    'pase a "ACD" de maner manual y pueda así recibir llamadas siempre que este asignado a las colas.</p>'

  BootstrapDialog.show({
    type: 'type-primary',
    title: 'Activar Llamadas',
    message: message,
    closable: true,
    buttons: [
      {
        label: '<i class="fa fa-check" aria-hidden="true"></i> Si',
        cssClass: 'btn-success',
        action: function (dialogRef) {
          if(vueFront.getRole !== 'user'){
            vueFront.remoteActiveCallsNameRole = 'user'
            vueFront.activeCalls('user')
          }else{
            closeNotificationBootstrap()
          }
        }
      },
      {
        label: '<i class="fa fa-times" aria-hidden="true"></i> No',
        cssClass: 'btn-danger',
        action: function (dialogRef) {
          if(vueFront.getRole !== 'backoffice'){
            vueFront.remoteActiveCallsNameRole = 'backoffice'
            vueFront.activeCalls()
          }else{
            closeNotificationBootstrap()
          }
        }
      },
      {
        label: '<i class="fa fa-sign-out" aria-hidden="true"></i> Cancelar',
        cssClass: 'btn-primary',
        action: function (dialogRef) {
          vueFront.remoteActiveCallsUserId = ''
          dialogRef.close()
        }
      }
    ]
  })
}

const changeRol = (userId) => {
  const message = 'Seleccione el rol que quiere asignar al usuario :'+
      '<br><br>'+
      '<div class="row">'+
      '<div class="col-md-4"><center><input type="radio" name="nameRole" value="user" checked="checked">User</center></div>'+
      '<div class="col-md-4"><center><input type="radio" name="nameRole" value="supervisor">Supervisor</center></div>'+
      '<div class="col-md-4"><center><input type="radio" name="nameRole" value="backoffice">BackOffice</center></div>'+
      '</div>'+
      '<br>'+
      '<b>Nota :</b> Utilizar esta opcion siempre y cuando el usuario no se encuentre en una cola.'

  BootstrapDialog.show({
    type: 'type-primary',
    title: 'Cambiar Rol',
    message: message,
    closable: true,
    buttons: [
      {
        label: 'Aceptar',
        cssClass: 'btn-success',
        action:  (dialogRef) => {
          let nameRole = $('input:radio[name=nameRole]:checked').val()
          vueFront.remoteActiveCallsUserId = userId
          vueFront.remoteActiveCallsNameRole = nameRole
          vueFront.activeCalls()
          //activeCalls(nameRole,userId)
          show_tab_list_user('list_users')
        }
      },
      {
        label: 'Cancelar',
        cssClass: 'btn-danger',
        action:  (dialogRef) => {
          dialogRef.close()
        }
      }
    ]
  })
}

const checkPassword = () => {if(vueFront.statusChangePassword == 0) changePassword()}

const changePassword = (userId = '',closable = false) => {
  if(userId === '') userId = vueFront.getUserId
  const token = $('input[name=_token]').val()
  const message = '<br>' +
    '<div class="row">' +
    '<div class="col-md-7">' +
    '<div class="col-md-6" >' +
    'Nueva Contraseña:' +
    '</div>' +
    '<div class="col-md-6">' +
    '<input type="password" class="form-control" style="border-radius: 7px" id="newPassword">' +
    '</div>' +
    '<br>' + '<br>' + '<br>' +
    '<div class="col-md-6">' +
    'Confirma Contraseña:' +
    '</div>' +
    '<div class="col-md-6">' +
    '<input type="password" class="form-control" style="border-radius: 7px" id="confirmPassword">' +
    '</div>' +
    '</div>' +
    '<div class="col-md-5">' +
    '<div class="col-md-12">' +
    'Una contraseña segura está compuesta de 8 a 12 caracteres.<br>' +
    'Una diferencia entre mayuscula y minuscula.<br>' +
    'Permite números , letras y símbolos del teclado.' +
    '</div>' +
    '</div>' +
    '</div>'

  BootstrapDialog.show({
    type: 'type-default',
    title: '<font style="color:red; text-align: center">Cambiar Contraseña</font>',
    message: message,
    closable: closable,
    buttons: [
      {
        label: 'Aceptar',
        cssClass: 'btn-danger',
        action: function (dialogRef) {
          var newPassword = $('#newPassword').val()
          var confirmPassword = $('#confirmPassword').val()

          if (confirmPassword != '' && newPassword != '') {
            if (confirmPassword == newPassword) {
              $.ajax({
                type: 'POST',
                url: 'modifyPassword',
                data: {
                  _token: token,
                  newPassword: newPassword,
                  userId: userId
                },
                success: function (data) {
                  if (data == 1) {
                    dialogRef.close()
                    vueFront.statusChangePassword = 1
                    mostrar_notificacion('success', 'El evento se realizo exitosamente!!!', 'Success', 2000, false, true)
                  } else {
                    mostrar_notificacion('error', 'Problemas de inserción a la base de datos', 'Error', 10000, false, true)
                  }
                }
              })
            } else {
              alert('Las contraseñas ingresadas no coinciden')
            }
          } else {
            alert('Por favor de llenar todos los campos')
          }
        }
      }
    ]
  })
}

/**
 * Created by jdelacruzc on 11/04/2017.
 *
 * [changeStatus description]
 * @return Cambia el estado del usuario
 */
const changeStatusUser = (userId, status) => {
  let token = $('input[name=_token]').val()
  let estado = (status === 'Inactivo')? 1 : 2
  let message = 'Deseas cambiar el estado del usuario ?' +
    '<br>' +
    '<b>Nota :</b> Esto afecta el estado (Activo o Inactivo).'

  BootstrapDialog.show({
    type: 'type-primary',
    title: 'Cambiar Estado',
    message: message,
    closable: true,
    buttons: [
      {
        label: 'Si',
        cssClass: 'btn-success',
        action: function (dialogRef) {
          $.ajax({
            type: 'POST',
            url: 'changeStatus',
            data: {
              _token:     token,
              userID:     userId,
              estadoID:   estado
            },
            success: function (data) {
              if (data === 1) {
                show_tab_list_user('list_users')
                dialogRef.close()
                mostrar_notificacion('success', 'Se cambio el estado del usuario!!!', 'Success', 2000, false, true)
              } else {
                mostrar_notificacion('error', 'Problemas al cambiar en la base de datos', 'Error', 10000, false, true)
              }
            }
          })
        }
      },
      {
        label: 'No',
        cssClass: 'btn-danger',
        action: function (dialogRef) {
          dialogRef.close()
        }
      }
    ]
  })
}

/**
 * Created by jdelacruzc on 11/04/2017.
 *
 * [createUser description]
 * @return Crea un usuario nuevo y refersca el datatable listuser
 */
function createUser () {
  const token = $('input[name=_token]').val()
  const message = '<br>' +
      '<div class="row">' +
      '<div class="col-md-12">' +
      '<div class="col-md-6" >' +
      'Primer Nombre:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="primerNombre" placeholder="Test">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Segundo Nombre:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="segundoNombre" placeholder="Test 2">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Apellido Paterno:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="apellidoPaterno" placeholder="Cosapi">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Apellido Materno:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="apellidoMaterno" placeholder="Cosapi 2">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Username:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="userName" placeholder="testCosapi">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Contraseña:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="password" class="form-control" style="border-radius: 7px" id="nuevaContraseña" placeholder="xxxxxx">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Correo:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<input type="text" class="form-control" style="border-radius: 7px" id="email" placeholder="cosapi@cosapidata.com.pe">' +
      '</div>' +
      '<br>' + '<br>' + '<br>' +
      '<div class="col-md-6">' +
      'Rol de Usuario:' +
      '</div>' +
      '<div class="col-md-6">' +
      '<select class="form-control" style="border-radius: 7px" id="slRol">' +
      '<option value="sinrol" selected>Seleccionar Aqui</option>' +
      '<option value="user">Usuario</option>' +
      '<option value="backoffice">BackOffice</option>' +
      '<option value="supervisor">Supervisor</option>' +
      '</select>' +
      '</div>' +
      '</div>' +
      '</div>'

  BootstrapDialog.show({
    type: 'type-primary',
    title: 'Crear Nuevo Usuario',
    message: message,
    closable: true,
    buttons: [
      {
        label: 'Aceptar',
        cssClass: 'btn-primary',
        action: function (dialogRef) {
          let primerNombre    = $('#primerNombre').val()
          let segundoNombre   = $('#segundoNombre').val()
          let apellidoPaterno = $('#apellidoPaterno').val()
          let apellidoMaterno = $('#apellidoMaterno').val()
          let userName        = $('#userName').val()
          let nuevaContraseña = $('#nuevaContraseña').val()
          let email           = $('#email').val()
          let role            = $('#slRol').val()

          if (primerNombre != '' && segundoNombre != '' && apellidoPaterno != '' && apellidoMaterno != '' && userName != '' && nuevaContraseña != '' && email != '' && role != 'sinrol') {
            $.ajax({
              type: 'POST',
              url: 'createUser',
              data: {
                _token:           token,
                primerNombre:     primerNombre,
                segundoNombre:    segundoNombre,
                apellidoPaterno:  apellidoPaterno,
                apellidoMaterno:  apellidoMaterno,
                userName:         userName,
                nuevaContraseña:  nuevaContraseña,
                email:            email,
                role:             role
              },
              success: function (data) {
                if (data == 1) {
                  show_tab_list_user('list_users')
                  dialogRef.close()
                  mostrar_notificacion('success', 'El usuario se registro correctamente!!!', 'Success', 2000, false, true)
                } else {
                  mostrar_notificacion('error', 'Problemas de inserción a la base de datos', 'Error', 10000, false, true)
                }
              }
            })
          } else {
            alert('Por favor de llenar todos los campos')
          }
        }
      },
      {
        label: 'Cancelar',
        cssClass: 'btn-primary',
        action: function (dialogRef) {
          dialogRef.close()
        }
      }
    ]
  })
}

/**
 * Created by jdelacruzc on 11/04/2017.
 *
 * [RoleTableHide description]
 * @return {Array} [Los roles con el cual me ocultaran las columnas]
 */
const RoleTableHide = () =>  ['user']