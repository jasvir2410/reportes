Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#tokenId').getAttribute('value')
Vue.component('v-select', VueSelect.VueSelect)
const socketAsterisk = io.connect(restApiDashboard, {
  'reconnection': true,
  'reconnectionAttempts': 15,
  'reconnectionDelay': 9000,
  'reconnectionDelayMax': 9000
})

const dashboard = new Vue({
  el: '#dashboard',
  data: {
    callsInbound: [],
    callsOutbound: [],
    callsWaiting: [],
    others: [],
    roleDefault: [
      'User'
    ],
    rolesPermission: [],

    agentStatusSummary: [],
    listProfileUsers: [],

    percentageAnswer: '',
    percentageUnanswer: '',
    avgWait: '',
    avgCallDuration: '',
    totalCallDurationInbound: '',
    totalCallDurationOutbound: '',

    answeredMonth: '0',
    answeredTimeMonth: '0',
    slaMonth: '0',

    slaDay: '0',
    answered: '0',
    abandoned: '0',
    totalCallsWaiting: '0',

    answeredTime: '0',
    abandonedTime: '0',

    abandonedSymbol: '',
    answeredSymbol: '',

    abandonedSecond: '',
    answeredSecond: '',

    ModalConnectionNodeJs: 'modal fade',
    nodejsServerName: '',
    nodejsServerMessage: ''
  },
  mounted () {
    this.loadListProfile()
    this.loadMetricasKpi(true)
  },
  methods: {
    loadMetricasKpi: async function (viewLoad) {
      if (viewLoad) {
        this.answered = '-'
        this.abandoned = '-'
        this.answeredTime = '-'
        this.abandonedTime = '-'
        this.slaDay = '-'
      }

      this.panelAgentStatusSummary()
      this.panelGroupStatistics()
      await this.loadAllKpisDay()
      await this.loadSlaDay()

      await this.loadAllKpisMonth()
      await this.loadSlaMonth()
    },

    loadTimeElapsed: function (index, dataDashboard, namePanel) {
      setInterval(async function () {
        if (dataDashboard[index]) {
          const horaBD = await this.getEventTime(index, dataDashboard, namePanel)
          const horaActual = (new Date()).getTime()
          const elapsed = differenceHours(horaActual - horaBD)
          if (elapsed != 'NaN:NaN:NaN') dataDashboard[index].timeElapsed = elapsed

        }
      }.bind(this), 1000)
    },

    getEventTime: function (index, dataDashboard, namePanel) {
      if (namePanel === 'callsInbound') return dataDashboard[index].inbound_start
      if (namePanel === 'callsOutbound') return dataDashboard[index].outbound_start
      if (namePanel === 'others') return dataDashboard[index].event_time
    },

    loadTimeElapsedEncoladas: function (index) {
      let calcular = () => {
        let horaInicio = (new Date()).getTime()
        let horaFin = this.callsWaiting[index].start_call
        this.callsWaiting[index].timeElapsed = differenceHours(horaInicio - horaFin)
      }
      setInterval(calcular(), 1000)
    },

    sendUrlRequest: async function (url, rangeDateSearch, nameAgent = '') {
      let parameters = {
        rangeDateSearch: rangeDateSearch,
        nameAgent: nameAgent
      }
      let response = await this.$http.post(url,parameters)
      return response.data
    },

    loadAllKpisDay: async function () {
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'forDay')

      this.abandoned = response[0].calls_abandone.message
      this.answered = response[0].calls_completed.message

      this.answeredTime = response[0].calls_completed_time.message
      this.answeredSecond = response[0].calls_completed_time.time
      this.answeredSymbol = response[0].calls_completed_time.symbol

      this.abandonedTime = response[0].calls_abandone_time.message
      this.abandonedSecond = response[0].calls_abandone_time.time
      this.abandonedSymbol = response[0].calls_abandone_time.symbol
    },

    loadSlaDay: function () {
      let answered = this.answered
      let answeredTime = this.answeredTime
      this.slaDay = 0
      if (answered !== 0) this.slaDay = ((answeredTime * 100) / answered).toFixed(2)
    },

    loadAllKpisMonth: async function () {
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'forMonth')

      this.answeredMonth = response[0].calls_completed.message
      this.answeredTimeMonth = response[0].calls_completed_time.message
    },

    loadSlaMonth: function () {
      let answeredMonth = this.answeredMonth
      let answeredTimeMonth = this.answeredTimeMonth
      this.slaMonth = 0
      if (answeredMonth !== 0) this.slaMonth = ((answeredTimeMonth * 100) / answeredMonth).toFixed(2)
    },

    loadListProfile: async function () {
      let response = await this.sendUrlRequest('dashboard_01/getListProfile')
      this.listProfileUsers = response.message
      await refreshDetailsCalls()
    },

    loadCallWaiting: function () {
      // this.queue = 15
    },

    panelAgentStatusSummary: async function () {
      let response = await this.sendUrlRequest('dashboard_01/panelAgentStatusSummary')
      let result = response.message
      let eventList = getRulers('event_id')
      result.forEach((item, index) => {
        item.color = eventList[item.event_id].color
        item.icon = eventList[item.event_id].icon
      })
      result = await orderObjects(result, 'event_id', eventList) //Ordena por regla establecida
      this.agentStatusSummary = result
    },

    panelGroupStatistics: async function () {
      let response = await this.sendUrlRequest('dashboard_01/panelGroupStatistics')
      this.avgWait = response.avgWait
      this.avgCallDuration = response.avgCallDuration
      this.percentageAnswer = response.percentageAnswer
      this.percentageUnanswer = response.percentageUnanswer
      this.totalCallDurationInbound = response.totalCallDurationInbound
      this.totalCallDurationOutbound = response.totalCallDurationOutbound
    },

    compareRole: function (role) {
      let Permission = false
      this.rolesPermission.forEach((index, value) => {
        index.forEach((roleName, roleIndex) => {
          let miniName = roleName.toLowerCase()
          if (role === miniName) Permission = true
        })
      })
      return Permission
    },

    loadRolePermission: function (val) {
      let cookieRole = replaceCookieArray(Cookies.get('roleCookie'))
      if (cookieRole) {
        Cookies.set('roleCookie', val, {expires: timeDaycookie, path: ''})
        this.rolesPermission.push(val)
        refreshDetailsCalls()
      } else {
        Cookies.set('roleCookie', val, {expires: timeDaycookie, path: ''})
        this.rolesPermission.push(val)
        refreshDetailsCalls()
      }
    }
  }
})

// Refresca la informacion de la tabla de DetailsCalls
const refreshDetailsCalls = () => {
  dashboard.callsInbound = []
  dashboard.callsOutbound = []
  dashboard.callsWaiting = []
  dashboard.others = []
  socketAsterisk.emit('createRoomDashboard', {nameProyect: nameProyecto})
  socketAsterisk.emit('listDataDashboard')
}


socketAsterisk.on('connect', function () {
  dashboard.nodejsServerName = 'Servidor Asterisk'
  dashboard.ModalConnectionNodeJs = 'modal fade'
  dashboard.nodejsServerMessage = 'Acabas de conectar con el Servidor Asterisk'
  console.log('Socket Asterisk connected!')
  dashboard.loadListProfile()
})

socketAsterisk.on('connect_error', function () {
  dashboard.nodejsServerName = 'Servidor Asterisk'
  dashboard.ModalConnectionNodeJs = 'modal show'
  let i = 9
  let refreshIntervalId = setInterval(() => {
    dashboard.nodejsServerMessage = `Fallo la conexión por el Servidor Asterik volveremos a reintentar en ${i} segundos!!!`
    i--
    if (i === 0) clearInterval(refreshIntervalId)
  }, 1000)
  console.log('socketAsterisk Connection Failed');
})

socketAsterisk.on('disconnect', function () {
  dashboard.nodejsServerName = 'Servidor Asterisk'
  dashboard.ModalConnectionNodeJs = 'modal show'
  dashboard.nodejsServerMessage = 'Acabas de perder conexión con el Asterisk !!!'
  console.log('socketAsterisk Disconnected');
})

socketAsterisk.on('AddCallWaiting', dataCallWaiting => {
  dashboard.callsWaiting.push(dataCallWaiting)
  dashboard.totalCallsWaiting = (dashboard.callsWaiting).length
})

socketAsterisk.on('RemoveCallWaiting', dataCallWaiting => {
  let numberPhone = dataCallWaiting
  dashboard.callsWaiting.forEach((item, index) => {
    if (item.number_phone === numberPhone) dashboard.callsWaiting.splice(index, 1)
  })
  dashboard.totalCallsWaiting = (dashboard.callsWaiting).length
})

socketAsterisk.on('RemoveOther', dataOther => removeDataDashboard(dataOther, dashboard.others, 'others'))
socketAsterisk.on('UpdateOther', dataOther => updateDataDashboard(dataOther, dashboard.others, 'others'))
socketAsterisk.on('AddOther', dataOther => AddDataDashboard(dataOther, dashboard.others, 'others'))

socketAsterisk.on('RemoveOutbound', dataOutbound => removeDataDashboard(dataOutbound, dashboard.callsOutbound, 'callsOutbound'))
socketAsterisk.on('UpdateOutbound', dataOutbound => updateDataDashboard(dataOutbound, dashboard.callsOutbound, 'callsOutbound'))
socketAsterisk.on('AddOutbound', dataOutbound => AddDataDashboard(dataOutbound, dashboard.callsOutbound, 'callsOutbound'))

socketAsterisk.on('RemoveInbound', dataInbound => removeDataDashboard(dataInbound, dashboard.callsInbound, 'callsInbound'))
socketAsterisk.on('UpdateInbound', dataInbound => updateDataDashboard(dataInbound, dashboard.callsInbound, 'callsInbound'))
socketAsterisk.on('AddInbound', dataInbound => AddDataDashboard(dataInbound, dashboard.callsInbound, 'callsInbound'))


isExistDuplicate = (data, dataDashboard) => {
  let exist = true
  let index = (dataDashboard.length)
  if (index > 0) dataDashboard.forEach((item, index) => {
    if (item.agent_name == data.agent_name) exist = false
  })
  return exist
}

AddDataDashboard = async (data, dataDashboard, namePanel) => {
  let exist = isExistDuplicate(data, dataDashboard)
  if (exist) {
    let index = (dataDashboard.length)
    let eventList = getRulers('event_id')
    data.color = eventList[data.event_id].color
    data.icon = eventList[data.event_id].icon
    data = addInformationProfile(data)
    dataDashboard.push(data)
    orderDashboard(dataDashboard, namePanel)
    dashboard.loadTimeElapsed(index, dataDashboard, namePanel)
    dataDashboard[index].total_calls = await getTotalCalls(data)
    dashboard.panelAgentStatusSummary()
    dashboard.panelGroupStatistics()
  }

}

updateDataDashboard = (data, dataDashboard, namePanel) => {
  dataDashboard.forEach(async (item, index) => {
    if (item.agent_name === data.agent_name) {
      if (item.event_id !== data.event_id) {
        let eventList = getRulers('event_id')
        data.color = eventList[data.event_id].color
        data.icon = eventList[data.event_id].icon
        data = addInformationProfile(data)
        dataDashboard.splice(index, 1, data)
        dashboard.loadMetricasKpi(false)
        dashboard.loadTimeElapsed(index, dataDashboard, namePanel)
        dataDashboard[index].total_calls = await getTotalCalls(data)
      }
      item.agent_annexed = data.agent_annexed
      item.event_name = data.event_name
      item.agent_status = data.agent_status
      orderDashboard(dataDashboard, namePanel)
    }
  })
  dashboard.panelAgentStatusSummary()
  dashboard.panelGroupStatistics()
}

addInformationProfile = (data) => {
  data.avatar = dashboard.listProfileUsers[data.agent_user_id]['avatar']
  data.nameComplete = dashboard.listProfileUsers[data.agent_user_id]['nameComplete']
  data.role = dashboard.listProfileUsers[data.agent_user_id]['role']
  return data
}

removeDataDashboard = (data, dataDashboard, namePanel) => {
  dataDashboard.forEach((item, index) => {
    if (item.agent_name === data.agent_name) {
      dataDashboard.splice(index, 1)
    }
  })
  dashboard.panelAgentStatusSummary()
}

getTotalCalls = async (data) => {
  let response = await dashboard.sendUrlRequest('dashboard_01/getQuantityCalls', '', data.agent_name)
  return response.message
}

differenceHours = (s) => {
  function addZ(n) {
    return (n < 10 ? '0' : '') + n
  }

  let ms = s % 1000
  s = (s - ms) / 1000
  let secs = s % 60
  s = (s - secs) / 60
  let mins = s % 60
  let hrs = (s - mins) / 60

  return addZ(hrs) + ':' + addZ(mins) + ':' + addZ(secs)
}

const orderDashboard = async (dataDashboard, namePanel) => {
  if (namePanel !== '') {
    let newObject = await orderObjects(dataDashboard, 'event_time') // Ordena alfabeticamente
    newObject = await orderObjects(newObject, 'event_id', getRulers('event_id')) //Ordena por regla establecida
    newObject = await orderObjects(newObject, 'agent_role', getRulers('agent_role')) //Ordena por regla establecida
    eval('dashboard.' + namePanel + '= newObject')
  }
}

const orderObjects = (object, column, rulers = '') => {
  let newObject = []
  let indexObject = []
  object.forEach(function (objectPrimary, indexPrimary, arrPrimary) {
    menorIndex = indexPrimary
    menorObject = objectPrimary
    object.forEach((objectSecond, indexSecond, arrSecond) => {
      if (indexObject.indexOf(menorIndex) < 0) {
        if (indexObject.indexOf(indexSecond) < 0) {
          if (menorIndex != indexSecond) {
            let primervalor = (rulers !== '') ? rulers[menorObject[column]].position : menorObject[column]
            let segundovalor = (rulers !== '') ? rulers[objectSecond[column]].position : objectSecond[column]
            if (primervalor === segundovalor) {
              if (indexSecond > menorIndex) {
                menorIndex = menorIndex
                menorObject = menorObject
              } else {
                menorIndex = indexSecond
                menorObject = objectSecond
              }
            } else if (primervalor > segundovalor) {
              menorIndex = indexSecond
              menorObject = objectSecond
            }
          }
        }
      } else {
        menorIndex = indexSecond
        menorObject = objectSecond
      }
    })
    indexObject.push(menorIndex)
    newObject[indexPrimary] = menorObject
  })
  return newObject
}

const getRulers = (action) => {
  let rulers = ''
  if (action === 'event_id') {
    rulers = {
      '12': {'icon': 'fa fa-volume-up', 'color': 'success', 'position': 1},  // Ring Inbound
      '16': {'icon': 'fa fa-bell-slash', 'color': 'danger', 'position': 2},  // Hold Inbound
      '8': {'icon': 'fa fa-phone', 'color': 'success', 'position': 3},  // Inbound
      '13': {'icon': 'fa fa-volume-up', 'color': 'primary', 'position': 4},  // Ring Outbound
      '17': {'icon': 'fa fa-bell-slash', 'color': 'danger', 'position': 5},  // Hold Outbound
      '9': {'icon': 'fa fa-headphones', 'color': 'warning', 'position': 6},  // Outbound
      '18': {'icon': 'fa fa-volume-up', 'color': 'default', 'position': 7},  //Ring Inbound Interno
      '22': {'icon': 'fa fa-bell-slash', 'color': 'default', 'position': 8},  //Hold Inbound Interno
      '19': {'icon': 'fa fa-phone', 'color': 'default', 'position': 9},  //Inbound Interno
      '24': {'icon': 'fa fa-volume-up', 'color': 'default', 'position': 10},  //Ring Inbound Transfer
      '26': {'icon': 'fa fa-bell-slash', 'color': 'default', 'position': 11},  //Hold Inbound Transfer
      '25': {'icon': 'fa fa-phone', 'color': 'default', 'position': 12},  //Inbound Transfer
      '21': {'icon': 'fa fa-volume-up', 'color': 'default', 'position': 13},  //Ring Outbound Interno
      '23': {'icon': 'fa fa-bell-slash', 'color': 'default', 'position': 14},  //Hold Outbound Interno
      '20': {'icon': 'fa fa fa-headphones', 'color': 'default', 'position': 15},  //Outbound Interno
      '27': {'icon': 'fa fa-volume-up', 'color': 'default', 'position': 10},  //Ring Outbound Transfer
      '28': {'icon': 'fa fa-bell-slash', 'color': 'default', 'position': 11},  //Hold Outbound Transfer
      '29': {'icon': 'fa fa-phone', 'color': 'default', 'position': 12},  //Outbound Transfer
      '1': {'icon': 'fa fa-fax', 'color': 'info', 'position': 16},  // ACD
      '7': {'icon': 'fa fa-suitcase', 'color': 'primary', 'position': 17},  // Gestión BackOffice
      '2': {'icon': 'fa fa-star', 'color': 'primary', 'position': 18},  // Break
      '4': {'icon': 'fa fa-cutlery', 'color': 'primary', 'position': 19}, // Refrigerio
      '3': {'icon': 'fa fa-asterisk', 'color': 'primary', 'position': 20}, // SSHH
      '5': {'icon': 'fa fa-retweet', 'color': 'danger', 'position': 21}, // Feedback
      '6': {'icon': 'fa fa-book', 'color': 'danger', 'position': 22}, // Capacitación
      '11': {'icon': 'fa fa-home', 'color': 'default', 'position': 23},  //Login
    }
  }

  if (action === 'agent_role') {
    rulers = {
      'user': {'position': 1},
      'backoffice': {'position': 2},
      'supervisor': {'position': 3},
      'admin': {'position': 4},
      'cliente': {'position': 5},
      'calidad': {'position': 6}
    }
  }
  return rulers
}

const replaceCookieArray = (cookie) => {
  if (cookie) {
    let firstCookie = cookie.replace(/"/g, '')
    let secondCookie = firstCookie.replace('[', '')
    let thirdCookie = secondCookie.replace(']', '')
    return thirdCookie.split(',')
  } else {
    return
  }
}

let cookieRole = replaceCookieArray(Cookies.get('roleCookie'))
if (cookieRole) {
  if (cookieRole.toString().length === 0) dashboard.roleDefault = ['User']
  else dashboard.roleDefault = cookieRole
} else {
  dashboard.roleDefault = ['User']
}