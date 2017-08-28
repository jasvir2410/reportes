'use strict'
Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#tokenId').getAttribute('value')

const socketIO = io.connect(restApiSails)
const socketSails = io.sails.connect(restApiSails)
io.sails.autoConnect = false
socketSails.reconnection = true
socketSails.reconnectionDelayMax = 5

const socketAsterisk = io.connect(restApiDashboard, {'reconnection': true, 'reconnectionAttempts' : 15, 'reconnectionDelay' : 9000,'reconnectionDelayMax' : 9000})

const vueFront = new Vue({
  el : '#frontAminLTE',
  data : {
    getUserId : '',
    getUsername : '',
    getNameComplete : '',
    getRole : '',
    getRemoteIp : '',
    getEventName : '',
    getEventId : '',
    statusChangePassword : '',
    statusChangeAssistance : '',
    statusAddAgentDashboard : '',
    statusQueueAddAsterisk : false,
    eventStatusPause : '',
    annexedStatusAsterisk : 0,
    annexed : 0,
    srcAvatar :'default_avatar.png',

    remotoReleaseUserId : 0,
    remotoReleaseUsername : 0,
    remotoReleaseAnnexed : 0,
    remotoReleaseStatusQueueRemove : false,
    remoteAgentHour : '',
    remoteActiveCallsUserId : '',
    remoteActiveCallsNameRole : '',

    quantityQueueAssign : 0,
    hourServer : '',
    textDateServer : '',
    dateServer : '',
    requiredAnnexed : false,
    differenceHour : '00:00:00',

    getListEvents : [],
    getAgentDashboard : [],

    assistanceTextModal : 'Entrada',
    assistanceHour : '',
    assistanceNextHour : '',

    nextEventId : '',
    nextEventName : '',
    routeAction : '/detalle_eventos/registrarDetalle',

    ModalLoading: 'modal fade',
    ModalAssistance : 'modal fade',
    ModalChangeStatus : 'modal fade',
    ModalReleasesAnnexed : 'modal fade',
    ModalConnectionNodeJs: 'modal fade',
    ModalStandByAssistance: 'modal fade',


    nodejsStatusSails: false,
    nodejsStatusAsterisk: false,

    nodejsServerName : '',
    nodejsServerMessage : '',

    titleStandBy : '',
    messageStandBy : ''
  },
  mounted(){
    this.loadVariablesGlobals()
  },
  methods :{

    sendUrlRequest : async function (url,parameters = {}){
      try {
        let response = await this.$http.post(url,parameters)
        return response.data
      }catch (error){ return error.status}
    },

    getAvatar : function (){
      let userId = this.getUserId
      let parameters = { userID : userId }
      this.$http.post('viewUsers', parameters).then(response => {
        this.srcAvatar = response.body[0].user_profile.avatar
      },response => console.log(response.body))
    },

    loadVariablesGlobals : async function () {
      let response = await this.sendUrlRequest('/getVariablesGlobals')
      this.getUserId = response.getUserId
      this.getUsername = response.getUsername
      this.getNameComplete = response.getNameComplete
      this.getRole = response.getRole
      this.getRemoteIp = response.getRemoteIp
      this.statusChangePassword = response.statusChangePassword
      this.statusChangeAssistance = response.statusChangeAssistance
      this.statusQueueAddAsterisk = response.statusQueueAddAsterisk
      //this.statusAddAgentDashboard = response.statusAddAgentDashboard
      this.requiredAnnexed = response.requiredAnnexed
      this.hourServer = response.hourServer
      this.textDateServer = response.textDateServer
      this.dateServer = response.dateServer
      this.annexed = response.annexed
      this.quantityQueueAssign = response.quantityQueueAssign
      this.getAgentDashboard = response.getAgentDashboard
      this.assistanceNextHour = response.assistanceNextHour
      await fechaActual()
      await horaActual()

      //Verificacion de marcado de asistencia y requerimiento de cambio de contraseña
      await this.verifyAssistance()

      if (this.annexed === 0 ) loadModule('agents_annexed')

      // Setea la etiqueta del estado actual cada vez que actualicen la pantalla del sistema
      let parameters = {userID: this.getUserId}
      ajaxNodeJs(parameters, '/detalle_eventos/getStatusActual', true)



      //Cargando image del profile_user
      this.getAvatar()

      //Cargando registro en Dahsboard
      let responseAddDashboard = await this.sendUrlRequest('/getStatusAddAgentDashboard')
      if(responseAddDashboard.statusAddAgentDashboard === false) {
        this.statusAddAgentDashboard = true
        let dataAddAgentDashboard = await this.sendUrlRequest('/getAgentDashboard')
        this.getAgentDashboard = dataAddAgentDashboard.statusAddAgentDashboard
        socketAsterisk.emit('createUserDashboard', this.getAgentDashboard)
      }
      else if(vueFront.getUserId) {
        this.statusAddAgentDashboard = true
        socketAsterisk.emit('createRoom', {agent_user_id : vueFront.getUserId})
      }

      if(this.statusAddAgentDashboard === '') mostrar_notificacion('warning', 'Tienes problemas con tu usuario, comunicate con el área de sistemas !!!', 'Warning', 0, false, true, 0)
    },

    verifyQueueAssign : function(){
      if (this.getRole === 'user') {
        if(this.quantityQueueAssign === false) {
          mostrar_notificacion('warning', 'Usted no tiene colas asignadas.', 'Ooops!!!', 10000, false, true)
          return false
        }
        return true
      }
      return true
    },

    loadModalStatus : async  function (){
      let response = ''
      let isVerifyAnnexed = false
      let isVerifyQueueAssign  = this.verifyQueueAssign()
      if (isVerifyQueueAssign) isVerifyAnnexed = this.verifyAnnexed()
      if (isVerifyAnnexed) response = await this.sendUrlRequest('list_event')
      if (isVerifyAnnexed) this.getListEvents = response.getListEvents
      if (isVerifyAnnexed) this.ModalChangeStatus = 'modal show'
    },

    verifyAnnexed : function(){
      if (this.getRole === 'user') {
        if(this.requiredAnnexed) {
          if (this.annexed == 0){
            closeNotificationBootstrap()
            mostrar_notificacion('warning', 'Usted debe seleccionar un anexo.', 'Ooops!!!', 10000, false, true)
            return false
          }
          return true
        }
        return false
      }
      return true
    },

    defineRoute : function(event){
        switch (event){
          case 'changeStatus' :
            if (this.getRole === 'user') {
              (this.statusQueueAddAsterisk === true)? this.routeAction = '/detalle_eventos/queuePause' : this.routeAction = '/detalle_eventos/queueAdd'
            }else{
              this.routeAction = '/detalle_eventos/registrarDetalle'
            }
            break
          case 'releasesAnnexed' :
            this.routeAction = '/anexos/liberarAnexoOnline'
            break
          case 'assignAnnexed' :
            this.routeAction = '/anexos/asignarAnexo'
            break
          case 'disconnectAgent' :
            (this.statusQueueAddAsterisk === true)? this.routeAction = '/detalle_eventos/queueRemove' : this.routeAction = '/anexos/logout'
            break
          case 'checkAssistance' :
            this.routeAction = '/detalle_eventos/registerAssistence'
            break
        }

    },

    changeStatus : function (eventId, eventName,eventStatusPause){
      this.ModalChangeStatus = 'modal fade'
      this.defineRoute('changeStatus')
      this.nextEventId = eventId
      this.nextEventName = eventName
      this.eventStatusPause = eventStatusPause
      this.remoteAgentHour = this.hourServer
      let parameters = this.loadParameters('changeStatus')
      ajaxNodeJs(parameters, this.routeAction, true, 2000)
    },


    assignAnnexed : function (){
      this.defineRoute('assignAnnexed')
      this.nextEventId = this.getEventId
      this.nextEventName = this.getEventName
      let parameters = this.loadParameters('assignAnnexed')
      ajaxNodeJs(parameters, this.routeAction, true, 2000)
      loadModule('agents_annexed')
    },

    liberarAnexos : function (){
      if(this.annexed != 0) this.ModalReleasesAnnexed = 'modal show'
      else mostrar_notificacion('warning', 'No tiene un anexo asignado', 'Warning', 10000, false, true)
    },

    releasesAnnexed : function (){
      this.defineRoute('releasesAnnexed')
      this.nextEventId = 11
      this.nextEventName = 'Login'
      this.remoteAgentHour = this.hourServer
      this.annexed = 0
      let parameters = this.loadParameters('releasesAnnexed')
      vueFront.ModalReleasesAnnexed = "modal fade"
      ajaxNodeJs(parameters, this.routeAction, true, 2000)
      loadModule('agents_annexed')
    },

    activeCalls : async function (){
      let parametersRole = this.loadParameters('activeCalls')
      let response = await this.sendUrlRequest('modifyRole',parametersRole)
      closeNotificationBootstrap()
      if(response === 1 ) {
        if(this.getUserId === this.remoteActiveCallsUserId) {
          this.getRole = this.remoteActiveCallsNameRole
          this.statusQueueAddAsterisk = false
          this.remoteActiveCallsNameRole = ''
          this.remoteActiveCallsUserId = ''
        }
        mostrar_notificacion('success', 'El cambio de rol se realizo exitosamente !!!', 'Success', 5000, false, true)
      }
      else {
        mostrar_notificacion('error', 'Problemas a la  hora de actualizar el rol en la base de datos', 'Error', 10000, false, true)
      }
    },

    disconnectAgent : function (){
      this.defineRoute('disconnectAgent')
      this.nextEventId = 15
      this.nextEventName = 'Desconectado'
      let parameters = this.loadParameters('disconnectAgent')
      ajaxNodeJs(parameters, this.routeAction, true, 2000)
    },

    verifyAssistance : async function(){
      if(this.statusChangeAssistance === true)this.loadModalCheckAssistance()
      else if(this.statusChangeAssistance != false) {
        this.titleStandBy = 'Ventana de Conexión'
        this.messageStandBy = 'Usted ingresara al menú del sistema en :'
        this.loadModalStandByAssistance()
      }
      else checkPassword()
    },

    loadModalCheckAssistance : async function (){
      let rankHours = await rangoHoras(this.hourServer)
      this.assistanceHour = rankHours[1]
      this.assistanceNextHour = rankHours[2]
      this.ModalAssistance = 'modal show'
    },

    loadModalStandByAssistance : function (){
      this.titleStandBy = 'Ventana de Conexión'
      this.messageStandBy = 'Usted ingresara al menú del sistema en :'
      this.ModalStandByAssistance = 'modal show'
      let refreshIntervalStandBy = setInterval(() => {
        this.differenceHour = restarHoras(this.hourServer,this.assistanceNextHour)
        if (this.hourServer >= this.assistanceNextHour){
          clearInterval(refreshIntervalStandBy)
          this.ModalStandByAssistance = 'modal fade'
          checkPassword()
        }
      },1000)
    },

    checkAssistance : function(){
      let route = (this.assistanceTextModal === 'Entrada')? 'checkAssistance' : 'disconnectAgent'
      this.defineRoute(route)
      if(route == 'checkAssistance'){
        this.nextEventId = 11
        this.nextEventName = 'Login'
      }
      let parameters = this.loadParameters(route)
      ajaxNodeJs(parameters, this.routeAction, true, 2000)
      this.ModalAssistance = 'modal fade'
    },

    loadParameters : function (actionEvent){
      let data = []

      if(actionEvent === 'activeCalls'){
        if(this.remoteActiveCallsNameRole === '') data = { nameRole : this.getRole, userId : this.getUserId }
        else data = {nameRole : this.remoteActiveCallsNameRole, userId : this.remoteActiveCallsUserId }
        return data
      }

      data = {
        userID : (this.remotoReleaseUserId === 0)? this.getUserId : this.remotoReleaseUserId,
        username : (this.remotoReleaseUsername === 0)? this.getUsername : this.remotoReleaseUsername,
        userRol : this.getRole,
        eventID : this.getEventId,
        eventName : this.getEventName,
        eventNextID : this.nextEventId,
        eventNextName : this.nextEventName,
        eventFechaHora : `${this.dateServer} ${this.remoteAgentHour}`,
        eventDateReally : `${this.dateServer} ${this.hourServer}`,
        eventIPCliente : this.getRemoteIp,
        eventAnnexed : (this.remotoReleaseAnnexed === 0)? this.annexed : this.remotoReleaseAnnexed,
        typeActionPostRequest : actionEvent,
        eventStatusPause : this.eventStatusPause,
        statusQueueRemove : (this.remotoReleaseStatusQueueRemove === false)? this.statusQueueAddAsterisk : this.remotoReleaseStatusQueueRemove
      }
      return data
    }
  }
})


socketAsterisk.on('connect', function() {
  vueFront.nodejsServerName = 'Servidor Asterisk'
  vueFront.ModalConnectionNodeJs = 'modal fade'
  vueFront.nodejsServerMessage = 'Acabas de conectar con el Servidor Asterisk'
  console.log('Socket Asterisk connected!')
  if(vueFront.getUserId) socketAsterisk.emit('createRoom', {agent_user_id : vueFront.getUserId})
})

socketAsterisk.on('connect_error', function(){
  vueFront.nodejsServerName = 'Servidor Asterisk'
  vueFront.ModalConnectionNodeJs = 'modal show'
  let i = 9
  let refreshIntervalId  = setInterval(()=> {
    vueFront.nodejsServerMessage = `Fallo la conexión por el Servidor Asterik volveremos a reintentar en ${i} segundos!!!`
    i--
    if(i === 0) clearInterval(refreshIntervalId)
  },1000)
  console.log('socketAsterisk Connection Failed');
})

socketAsterisk.on('disconnect', function () {
  vueFront.nodejsServerName = 'Servidor Asterisk'
  vueFront.ModalConnectionNodeJs = 'modal show'
  vueFront.nodejsServerMessage = 'Acabas de perder conexión con el Asterisk !!!'
  console.log('socketAsterisk Disconnected');
})

// Cambia la etiqueta de estado actual cuando este recibe o realiza un llamada
socketAsterisk.on('statusAgent',  (data) => {
  vueFront.sendUrlRequest('/updateStatusAddAgentDashboard')
  vueFront.statusAddAgentDashboard = data.statusAddAgentDashboard
  vueFront.getEventName = data.eventName
  vueFront.getEventId = data.eventId
  vueFront.annexedStatusAsterisk = data.annexedStatusAsterisk
  vueFront.nextEventId = ''
  vueFront.nextEventName = ''
})

// Cambia la etiqueta del estado actual cada vez que realiza un cambio de estado
socketSails.on('statusSails', function (data) {
  vueFront.ModalLoading = 'modal fade'
  vueFront.getEventName = data.eventName
  vueFront.getEventId = data.eventId
  vueFront.nextEventId = ''
  vueFront.nextEventName = ''
})

socketSails.on('connect', function () {
  vueFront.nodejsServerName = 'Servidor Sails'
  vueFront.ModalConnectionNodeJs = 'modal fade'
  vueFront.nodejsServerMessage = 'Acabas de conectar con el Servidor Sails'
  console.log('Socket Sails.io connected!')
})

socketSails.on('disconnect', function () {
  vueFront.nodejsServerName = 'Servidor Sails'
  vueFront.ModalConnectionNodeJs = 'modal show'
  vueFront.nodejsServerMessage = 'Acabas de perder conexión con el Servidor Sails'
  console.log('Socket Sails.io desconectado!')
})
