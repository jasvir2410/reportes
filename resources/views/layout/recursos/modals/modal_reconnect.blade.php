<!-- Modal -->
<div :class="ModalConnectionNodeJs" role="dialog" data-backdrop="static" data-keyboard="false" v-if="ModalConnectionNodeJs !== 'modal fade'">
  <div class="modal-dialog">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h4 class="modal-title">@{{ nodejsServerName }}</h4>
      </div>
      <div class="modal-body">
        <div class="img-responsive "><center> @{{ nodejsServerMessage }}</center></div>
        <br>
      </div>
    </div>
  </div>
</div>
