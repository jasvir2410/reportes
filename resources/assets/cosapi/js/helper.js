'use strict';

/*!
 * Cosapi v1.0 : JQuery Personalizado
 * Copyright 2015
 * Alan Cornejo
 */

// Función que carga HTML en una etiqueta div de ID => container
const loadModule = (idOptionMenu) => {
  let url = idOptionMenu
  $.ajax({
    type: 'POST',
    url: url,
    data: {
      _token: $('input[name=_token]').val(),
      url: url
    },
    beforeSend: function (data) {
      let divLoading = '<div class="loading" id="loading" style="display: inline;"><li></li><li></li><li></li><li></li><li></li></div>'
      $('#container').html(divLoading)
    },
    success: function (data) {
      $('#container').html(data)
      $('#urlsistema a').remove()
      $('#urlsistema').append('<a href="#" id="' + url + '" class="reportes">' + $('#' + url).text() + '</a>')
    },
    error: function (data) {
      $('#container').html('problemas para actualizar')
    },
    complete: function () {
      $('#loading').hide()
    }
  })
}

/**
 * [get_data_filters Función que configura datos a enviar en las consultas]
 * @param  {String} evento [Tipo de reporte a cargar en la vista]
 * @return {Array}         [Devuelve parametros configurados]
 */
const get_data_filters = (evento) => {
  let rankHour = 1800
  if ($('select[name=rankHour]').length) rankHour = $('select[name=rankHour]').val()
  let data = {
    _token: $('input[name=_token]').val(),
    fecha_evento: $('input[name=fecha_evento]').val(),
    rank_hour: rankHour,
    evento: evento
  }
  return data
}

/**
 * [exportar description]
 * @param  {[type]} format_export [description]
 * @return {[type]}               [description]
 */
const exportar = (format_export) => {
  let days      = $('#texto').val()
  let event     = $('#hidEvent').val()
  let rankHour  = 1800
  if($('#rankHour').length >0 ) rankHour = $('#rankHour').val()
  export_ajax('POST', event, format_export, days,rankHour)
}

/**
 * [export_ajax description]
 * @param  {[type]} type          [Define si el envio de datos es por POST o GET]
 * @param  {String} url           [Ruta a la cual se van a solicitar los datos]
 * @param  {String} format_export [Formato en el cual se va a exportar el archivo]
 * @param  {String} days          [Fecha de consulta de datos]
 */
const export_ajax = (type, url, format_export = '', days = '',rankHour = 1800) => {
  const dialog = cargar_dialog('primary', 'Download', 'Cargando el Excel', false)
  const token = $('input[name=_token]').val()
  $.ajax({
    type: type,
    url: url,
    cache: false,
    data: {
      _token          : token,
      format_export   : format_export,
      days            : days,
      rank_hour       : rankHour
    },
    beforeSend: function (data) {
      dialog.open()
    },
    success: function (data) {
      var array_url = data.path
      for (var i = 0; i < array_url.length; i++) {
        downloadURL(array_url[i], i)
      }
    },
    complete: function () {
      dialog.close()
    }
  })
}

/**
 * [downloadURL Iframe creado dinamicamente para realizar la descarga de archivos a la hora de exportar]
 * @param  {String} url   [Dirección en donde se encuentra el archivo descargado]
 * @param  {String} index [Indentificador de los frame, para que no tengan el mismo nombre como ID]
 */
const downloadURL = (url, index) => {
  const hiddenIFrameID = `hiddenDownloader${index}`
  let iframe = document.createElement('iframe')
  iframe.id = hiddenIFrameID
  iframe.style.display = 'none'
  document.body.appendChild(iframe)
  iframe.src = url
}

/**
 * [cargar_dialog Función que muestra ventana emergente indicando que se va a realizar una descargar de algun archivo]
 * @param  {String} new_string [Tipo de ventan emergente a mostrar]
 * @param  {String} title    [Titulo de la ventana emergente]
 * @param  {String} message  [Mensaje que se mostrara en la ventana emergente]
 * @param  {String} closable [Dato que nos indica que si podemos cerrar la ventana]
 * @return {[booleam]}          [description]
 */
const cargar_dialog = (new_type, title, messagedialog, closable) => {
  let message = $('<div></div>')
  message.append('<center><b>Porfavor no cerrar la ventana</b></br><img src="../img/loading_bar_cicle.gif" /></center>')
  const dialog = new BootstrapDialog({
    size: BootstrapDialog.SIZE_SMALL,
    type: 'type-' + new_type,
    title: title,
    message: message,
    closable: closable
  })
  return dialog
}

/**
 * [cargar_ajaxDIV Función para cargar los datos en la vista]
 * @param  {String}  type     [Tipo de envío de datos: POST o GET]
 * @param  {String}  url      [Ruta a la cual se consultara los datos]
 * @param  {String}  nameDiv  [El nombre del Div en el cual se cargaran los datos]
 * @param  {String}  msjError [Texto qu se mostrar si ocurre algún tipo de error a la hora de cargar los datos]
 * @param  {Boolean} before   [Datos que nos dira si se ebe mostrar la imagen de cargando]
 */
const cargar_ajaxDIV = (type, url, nameDiv, msjError, before = false) => {
  const image_loading = '<div class="loading" id="loading"><li></li><li></li><li></li><li></li><li></li></div>'
  $.ajax({
    type: type,
    url: url,
    cache: false,
    dataType: 'HTML',
    beforeSend: function () {
      if (before == true) {
        $('#' + nameDiv).html(image_loading)
      }
    },
    success: function (data) {
      $('#loading').hide()
      $('#' + nameDiv).html(data)
    },
    error: function (data) {
      $('#' + nameDiv).html(msjError)
    }
  })
}

/**
 * [refresh_information Función que refresca la información del dahsboard 2]
 * @param  {String} evento [Tipo de reporte a visualizar: day, week, mounth]
 */
const refresh_information = (evento) => {
  BootstrapDialog.confirm('¿Está seguro que quieres actualizar los datos del monitor?', function (result) {
    if (result) {
      $('#hidReporttype').val(evento)
      detalle_kpi_dashboard_02(evento)
    } else {
      $('#ulOptions li').removeClass('active')
      var idevento = $('#hidReporttype').val()
      $('#' + idevento).addClass('active')
    }
  })
}

/**
 * [detalle_kpi_dashboard_02 Función que carga los ddatos del KPI ubicados en el Dashboard2]
 * @param  {string} evento [Tipo de reporte que se quiere cargar: day, week, mounth]
 * @return {[view]}          [Vista con los datos dl reporte]
 */
const detalle_kpi_dashboard_02 = (evento) => {
  return cargar_ajaxDIV('GET', 'dashboard_02/detail_kpi/' + evento, 'detail_kpi', 'Problema para actualizar el KPI de agentes', true)
}

/**
 * [create_sound_bite Función que reproduce el audio para llamadas en cola]
 * @param  {String} sound [Ruta donde se encuentra ubicado nuestro audio]
 */
const create_sound_bite = (sound) => {
  const html5_audiotypes = {
    'mp3': 'audio/mpeg',
    'mp4': 'audio/mp4',
    'ogg': 'audio/ogg',
    'wav': 'audio/wav'
  }
  let html5audio = document.createElement('audio')
  if (html5audio.canPlayType) { // Comprobar soporte para audio HTML5
    for (var i = 0; i < arguments.length; i++) {
      var sourceel = document.createElement('source')
      sourceel.setAttribute('src', arguments[i])
      if (arguments[i].match(/.(w+)$/i)) sourceel.setAttribute('type', html5_audiotypes[RegExp.$1])
      html5audio.appendChild(sourceel)
    }
    html5audio.load()
    html5audio.playclip = function () {
      html5audio.pause()
      html5audio.currentTime = 0
      html5audio.play()
    }
    return html5audio
  } else return {playclip: function () { throw new Error('Su navegador no soporta audio HTML5') }}
}

/**
 * [validar_sonido Funcion que valida la cantidad de llamadas en cola par activar el sonido del monitor]
 */
const validar_sonido = () => {
  let click2 = create_sound_bite('/cosapi/sonidos/alerta_principal.mp3')
  let encoladas = $('#count_encoladas').text()
  if (encoladas >= 2) click2.playclip()
  setTimeout('validar_sonido()', 4000)
}

// Función que envía parametros a rutas en NodeJS
const ajaxNodeJs = (parameters, ruta, notificacion, time) => {
  vueFront.ModalLoading = 'modal show'
  socketSails.get(ruta, parameters, function (resData, jwRes) {
    vueFront.ModalLoading = 'modal fade'
    mostrar_notificacion(resData['Response'], resData['Message'], resData['Response'].charAt(0).toUpperCase() + resData['Response'].slice(1), time, false, true, 2000)
    //Muestra notificaciones al hacer QueueAdd o QueueRemove
    if(resData['DataQueue'] != null) loadMultiNotification(resData,time,parameters)
    if(resData['Response'] == 'success')eventPostExecuteActionSuccess(parameters)
    if(resData['Response'] == 'error')eventPostExecuteActionError(parameters)
  })
}

// Función que realiza Actividades como resultado de un evento realizado con el NodeJS
const eventPostExecuteActionSuccess = (parameters) => {
  switch (parameters['typeActionPostRequest']){
    case 'changeStatus':
      if(parameters['eventNextID'] == 1 && vueFront.getRole === 'user') setQueueAdd(true)
      break
    case 'assignAnnexed':
      vueFront.annexed = vueFront.remotoReleaseAnnexed
      vueFront.remotoReleaseAnnexed = 0
      //socketAsterisk.emit('createRoom', vueFront.annexed)
      break
    case 'releasesAnnexed':
      vueFront.remotoReleaseUserId = 0
      vueFront.remotoReleaseUsername = 0
      vueFront.remotoReleaseAnnexed = 0
      vueFront.remotoReleaseStatusQueueRemove = false
      socketAsterisk.emit('leaveRoom', vueFront.annexed)
      vueFront.annexed = 0
      setQueueAdd(false)
      break
    case 'disconnectAgent':
      vueFront.titleStandBy = 'Ventana de Desconexión'
      vueFront.messageStandBy = 'Estamos procesando la salida del sistema, espere :'
      vueFront.assistanceNextHour = '00:00:04'
      vueFront.hourServer = '00:00:00'
      vueFront.loadModalStandByAssistance()
      setTimeout('eventLogout()', 4000)
      break
    case 'checkAssistance':
      vueFront.statusChangeAssistance = false
      if(parameters['eventFechaHora'] > vueFront.hourServer ) {
        vueFront.statusChangeAssistance = `stand_by&${vueFront.remoteAgentHour}`
        vueFront.assistanceNextHour = vueFront.remoteAgentHour
        vueFront.loadModalStandByAssistance()
      }else {checkPassword()}
      break
  }
}

const eventPostExecuteActionError = function(parameters){
  switch (parameters['typeActionPostRequest']){
    case 'assignAnnexed':
      vueFront.remotoReleaseAnnexed = 0
      break
    case 'checkAssistance':
      vueFront.nextEventId = ''
      vueFront.nextEventName = ''
      break
  }
}
// Función que muestra multiples Notification en base a lo devuelto por el NodeJs
const loadMultiNotification = (resData,time,parameters) => {
  let arrayMessage = resData['DataQueue']
  let message = ''
  let messageSuccess = ''
  let messageError = ''
  let messageWarning = ''
  for (let posicion = 0; posicion < arrayMessage.length; posicion++) {
    switch (arrayMessage[posicion]['Response']){
      case 'Success' :
        messageSuccess = messageSuccess + arrayMessage[posicion]['Message']
        if (posicion != ((arrayMessage.length) - 1)) messageSuccess = messageSuccess + '<br>'
        break
      case 'Warning' :
        messageWarning = messageWarning + arrayMessage[posicion]['Message']
        if (posicion != ((arrayMessage.length) - 1)) messageWarning = messageWarning + '<br>'
        break
      case 'Error' :
        messageError = messageError + arrayMessage[posicion]['Message']
        if (posicion != ((arrayMessage.length) - 1)) messageError = messageError + '<br>'
        break
    }
  }
  if (messageSuccess !== '') {
    mostrar_notificacion('success', messageSuccess, 'Success', time, false, true, 2000)
    if (parameters['typeActionPostRequest'] === 'changeStatus') setQueueAdd(true)
    if (parameters['typeActionPostRequest'] === 'releasesAnnexed') setQueueAdd(false)
  }
  if (messageError !== '') mostrar_notificacion('error', messageError, 'Error', 0, false, true, 0)
  if (messageWarning !== '') mostrar_notificacion('warning', messageWarning, 'Warning', 0, false, true, 0)
}

// Función que actualiza la sessión de QueueAdd
const setQueueAdd = (queueAdd) => {
  const token = $('input[name=_token]').val()
  $.ajax({
    type: 'POST',
    url: 'setQueueAdd',
    data: {
      _token: token,
      QueueAdd: queueAdd
    },
    success: function (data) {
      vueFront.statusQueueAddAsterisk = data
    }
  })
}

// Función que redirecciona a la ventana Login matando las sesiones existentes
const eventLogout = () => location.href = 'logout'

// Función que genera el rango de horas para la marcacion de salida del agente
const rangoHoras = (hour_actually) => {
  let array = hour_actually.split(':')
  let secondBefore, secondAfter = '00'
  let hour, beforeHour, afterHour, minutoBefore, minutoAfter, hourAfter = ''
  if (array[1] >= 30) {
    if (array[0] != 23) {
      minutoBefore = '30'
      minutoAfter = '00'
      hourAfter = ceroIzquierda(parseInt(array[0]) + 1)
    } else {
      minutoBefore = '30'
      minutoAfter = '59'
      secondAfter = '59'
      hourAfter = '23'
    }
  } else {
    minutoBefore = '00'
    minutoAfter = '30'
    hourAfter = ceroIzquierda(parseInt(array[0]))
  }
  beforeHour = (array[0] + ':' + minutoBefore + ':' + secondBefore).trim()
  hour = hour_actually.trim()
  afterHour = (hourAfter + ':' + minutoAfter + ':' + secondAfter).trim()
  return [beforeHour, hour, afterHour]
}

// Función que completa con cero a la izquierda una variable
const ceroIzquierda = (numero) => {
  if (numero <= 9) numero = '0' + numero
  return numero
}

// Función que crea las Notification
const mostrar_notificacion = (type, mensaje, titulo, time, duplicate, close, extendtime) => {
  let position = ''
  switch (type){
    case 'success' :
      position = 'toast-top-right'
      break
    case 'warning' :
      position = 'toast-top-left'
      break
    case 'error' :
      position = 'toast-top-center'
      break
    default :
      position = 'toast-bottom-right'
      break
  }
  toastr.options = {
    'closeButton': close,
    'debug': false,
    'newestOnTop': true,
    'progressBar': true,
    'positionClass': position,
    'preventDuplicates': duplicate,
    'onclick': null,
    'showDuration': '300',
    'hideDuration': '800',
    'timeOut': time,
    'extendedTimeOut': extendtime,
    'showEasing': 'swing',
    'hideEasing': 'linear',
    'showMethod': 'fadeIn',
    'hideMethod': 'fadeOut'
  }

  Command: toastr[type](mensaje, titulo)
}

// Muestra la hora actual del sistema cada 1 segundo
const horaActual = () => {
  setTimeout(function(){
    let hourServer = vueFront.hourServer.split(':')
    let hora = parseInt(hourServer[0])
    let minuto = parseInt(hourServer[1])
    let segundo = parseInt(hourServer[2])
    segundo = segundo + 1
    if (segundo == 60) {
      minuto = minuto + 1
      segundo = 0
      if (minuto == 60) {
        minuto = 0
        hora = hora + 1
        if (hora == 24) {
          hora = 0
        }
      }
    }
    let str_hora = ''
    let str_minuto = ''
    let str_segundo = ''
    str_hora = new String(hora)
    str_minuto = new String(minuto)
    str_segundo = new String(segundo)
    if (str_hora.length == 1) hora = '0' + hora
    if (str_minuto.length == 1) minuto = '0' + minuto
    if (str_segundo.length == 1) segundo = '0' + segundo
    vueFront.hourServer = hora + ':' + minuto + ':' + segundo
    horaActual()
  }, 1000)
}

// Muestra la fecha actual en la cabecera del sistema.
const fechaActual = () => {
  let dateServer = vueFront.textDateServer.split('-')
  vueFront.textDateServer = nombre_dia(parseInt(dateServer[2])) + ' ' + parseInt(dateServer[0]) + ' de ' + nombre_mes(parseInt(dateServer[1]))
}

// Función que retorna nombre del mes, en base al número enviado
const nombre_mes = (mes) => {
  let nombre_mes
  switch (mes) {
    case 1 :
      nombre_mes = 'Ene'
      break
    case 2 :
      nombre_mes = 'Feb'
      break
    case 3 :
      nombre_mes = 'Mar'
      break
    case 4 :
      nombre_mes = 'Abr'
      break
    case 5 :
      nombre_mes = 'May'
      break
    case 6 :
      nombre_mes = 'Jun'
      break
    case 7 :
      nombre_mes = 'Jul'
      break
    case 8 :
      nombre_mes = 'Ago'
      break
    case 9 :
      nombre_mes = 'Sep'
      break
    case 10 :
      nombre_mes = 'Oct'
      break
    case 11 :
      nombre_mes = 'Nov'
      break
    case 12 :
      nombre_mes = 'Dic'
      break
  }
  return nombre_mes
}

// Función que retorna nombre del día, en base al número enviado
const nombre_dia = (dia) => {
  let nombre_dia
  switch (dia) {
    case 0 :
      nombre_dia = 'Dom'
      break
    case 1 :
      nombre_dia = 'Lun'
      break
    case 2 :
      nombre_dia = 'Mar'
      break
    case 3 :
      nombre_dia = 'Mie'
      break
    case 4 :
      nombre_dia = 'Jue'
      break
    case 5 :
      nombre_dia = 'Vie'
      break
    case 6 :
      nombre_dia = 'Sab'
      break
  }
  return nombre_dia
}

// Función que resta dos rango de horas
const restarHoras = (inicio, fin) => {
  inicio = inicio.split(':')
  fin = fin.split(':')
  let inicioHoras = parseInt(inicio[0]) * 3600
  let inicioMinutos = parseInt(inicio[1]) * 60
  let inicioSegundos = parseInt(inicio[2])
  let iniciototal = inicioHoras + inicioMinutos + inicioSegundos
  let finHoras = parseInt(fin[0]) * 3600
  let finMinutos = parseInt(fin[1]) * 60
  let finSegundos = parseInt(fin[2])
  let fintotal = finHoras + finMinutos + finSegundos
  let diferenciatotal = fintotal - iniciototal
  let diferenciaHoras = parseInt(diferenciatotal / 3600)
  let diferenciaMinutos = parseInt((diferenciatotal - (diferenciaHoras * 3600)) / 60)
  let diferenciaSegundos = parseInt(diferenciatotal - ((diferenciaHoras * 3600) + (diferenciaMinutos * 60)))
  return ceroIzquierda(diferenciaHoras) + ':' + ceroIzquierda(diferenciaMinutos) + ':' + ceroIzquierda(diferenciaSegundos)
}

/**
 * Created by jdelacruzc on 18/04/2017.
 *
 * [filterLetter description]
 * @return Solo permite letras y letras con acentos en los inputs
 */
function filterLetter(e){
  const key = e.keyCode || e.which
  const board = String.fromCharCode(key).toLowerCase()
  const letter = " áéíóúabcdefghijklmnñopqrstuvwxyz"
  const specials = "8-37-39-46"
  let specialskey = false
  for(let i in specials){
    if(key === specials[i]){
      specialskey = true
      break
    }
  }
  if(letter.indexOf(board) === -1 && !specialskey){
    return false
  }
}

/**
 * Created by jdelacruzc on 19/04/2017.
 *
 * [BlockCopyPaste description]
 * @return Bloquea el Ctrl C y Ctrl V
 */
const BlockCopyPaste = (e) =>{
  if(e.ctrlKey === true && (e.which === 118 || e.which === 86)) return false
}

/**
 * Created by jdelacruzc on 19/04/2017.
 *
 * [filterNumber description]
 * @return Solo permite ingresar numeros
 */
const filterNumber = (e) =>{
  let key = window.Event ? e.which : e.keyCode
  return (key >= 48 && key <= 57 || key === 8 || key === 9)
}

// Función que cierra todos los BootstrapDialog abiertos
const closeNotificationBootstrap = () => {
  $.each(BootstrapDialog.dialogs, function (id, dialog) {
    dialog.close()
  })
}
