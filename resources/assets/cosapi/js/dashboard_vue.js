Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#tokenId').getAttribute('value')
const socket = io.connect(restApiDashboard, { 'forceNew': true })
const dashboard = new Vue({
  el: '#dashboard',
  data: {
    agents: [],
    encoladas: [],

    slaDay: '0',
    answered: '0',
    abandoned: '0',
    callWaiting: '0',

    answeredTime: '0',
    abandonedTime: '0',

    abandonedSymbol: '',
    answeredSymbol: '',

    abandonedSecond: '',
    answeredSecond: ''
  },
  mounted(){
    this.loadMetricasKpi(true)
  },
  methods:{
    loadMetricasKpi:  async function (viewLoad){
      if (viewLoad) {
        this.answered = '-'
        this.abandoned = '-'
        this.answeredTime = '-'
        this.abandonedTime = '-'
        this.slaDay   = '-'
      }
      console.log('aaa')

      let answered = await this.loadAnswered()
      let abandoned = await this.loadAbandoned()
      let answeredTime = await this.loadAnsweredTime()
      let abandonedTime = await this.loadAbandonedTime()
      let slaDay = await this.loadSlaDay()
    },

    loadTimeElapsed: function(index){
        setTimeout(function(){
          let horaInicio =  (new Date()).getTime()
          let horaFin = this.agents[index].star_call_inbound
          this.agents[index].timeElapsed = restarHoras(horaInicio - horaFin)
          this.loadTimeElapsed(index)
        }.bind(this), 1000)
    },

    loadTimeElapsedEncoladas: function(index){
      let calcular = () => {
        let horaInicio =  (new Date()).getTime()
        let horaFin = this.encoladas[index].start_call
        this.encoladas[index].timeElapsed = restarHoras(horaInicio - horaFin)
      }
      setTimeout(calcular(), 1000)
    },

    sendUrlRequest: async function (url, type, actionTime = false){
      let parameters = {
        type : type,
        time : actionTime
      }
      let response = await this.$http.post(url, parameters)
      return response.data
    },

    loadAnswered: async function(){
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'calls_completed')
      this.answered = response.message
    },

    loadAbandoned: async function(){
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'calls_abandone')
      this.abandoned = response.message
    },

    loadAnsweredTime: async function(){
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'calls_completed', 'true')
      this.answeredTime = response.message
      this.answeredSecond = response.time
      this.answeredSymbol = response.symbol
    },

    loadAbandonedTime: async function(){
      let response = await this.sendUrlRequest('dashboard_01/getEventKpi', 'calls_abandone', 'true')
      this.abandonedTime = response.message
      this.abandonedSecond = response.time
      this.abandonedSymbol = response.symbol
    },

    loadSlaDay: function(){
      answered      = this.answered
      answeredTime  = this.answeredTime
      if (answered !== 0) this.slaDay = ((answeredTime * 100)/answered).toFixed(2)
    },

    loadCallWaiting: function(){
      //this.queue = 15
    }
  }
})


//Refresca la informacion de la tabla de DetailsCalls
const refreshDetailsCalls = () => socket.emit('listAgentConnect')
refreshDetailsCalls()

socket.on('UpdateMetricasKpi', data => dashboard.loadMetricasKpi(false))
socket.on('UpdateTotalCalls', data => dashboard.loadMetricasKpi(false))

socket.on('AddCallWaiting', data => {
  dashboard.encoladas.push(data.CallWaiting)
  dashboard.callWaiting = (dashboard.encoladas).length
})

socket.on('RemoveCallWaiting', data => {
  let numberPhone = data.CallWaiting
  dashboard.encoladas.forEach((item, index) => {
    if (item.number_phone === numberPhone) dashboard.encoladas.splice(index, 1)
  })
  dashboard.callWaiting = (dashboard.encoladas).length
})

socket.on('QueueMemberAdded', data => {
  let dataAgent = data.QueueMemberAdded
  let numberAnnexed = dataAgent.number_annexed
  const actionPush = updateDataAgent(numberAnnexed, dataAgent)
  if (actionPush === true) dashboard.agents.push(data.QueueMemberAdded)
  if (dataAgent.event_id != 11) dashboard.loadTimeElapsed((dashboard.agents.length)-1)
})

socket.on('QueueMemberRemoved', data => {
  updateDataAgent(data.NumberAnnexed, '')
})

socket.on('QueueMemberChange', data => {
  let dataAgent = data.QueueMemberChange
  let numberAnnexed = dataAgent.number_annexed
  updateDataAgent(numberAnnexed, dataAgent)
  if (dataAgent.event_id != 11) dashboard.loadTimeElapsed((dashboard.agents.length)-1)
})


function updateDataAgent(numberAnnexed, dataAgent){
  actionPush = true
  dashboard.agents.forEach((item, index) => {
    if (item.number_annexed === numberAnnexed) {
      actionPush = false
      if (dataAgent){
        dashboard.agents.splice(index, 1, dataAgent)
      }else{
        dashboard.agents.splice(index, 1)
      }
    }
  })

  return actionPush
}

function restarHoras (s) {
  function addZ(n) {
    return (n<10? '0':'') + n
  }

  let ms = s % 1000
  s = (s - ms) / 1000
  let secs = s % 60
  s = (s - secs) / 60
  let mins = s % 60
  let hrs = (s - mins) / 60

  return addZ(hrs) + ':' + addZ(mins) + ':' + addZ(secs)
}
