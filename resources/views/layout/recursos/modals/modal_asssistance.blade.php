<!-- Modal -->
<div :class="ModalAssistance" data-backdrop="static" data-keyboard="false" role="dialog" v-if="ModalAssistance !== 'modal fade'">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="panel panel-primary">
            <div class="panel-heading">
                <button type="button" class="close" @click="ModalAssistance = 'modal fade'" v-show="assistanceTextModal === 'Salida'"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Control de assistance</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" >
                        <p>Por favor de seleccionar la hora correspondiente a su @{{ assistanceTextModal }} .</p>
                        <br><br>
                        <div class="row">
                            <div class="col-md-6"><center><input type="radio" name="rbtnHour" id="rbtnHour" v-bind:value="assistanceHour" v-model="remoteAgentHour">@{{ assistanceHour }}</center></div>
                            <div class="col-md-6"><center><input type="radio" name="rbtnHour" id="rbtnHour_after" v-bind:value="assistanceNextHour" v-model="remoteAgentHour">@{{ assistanceNextHour }}</center></div>
                        </div>'
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" @click="checkAssistance">Aceptar</button> <button class="btn btn-danger" @click="ModalAssistance = 'modal fade'" v-show="assistanceTextModal === 'Salida'">Cancelar</button>
            </div>
        </div>
    </div>
</div>
