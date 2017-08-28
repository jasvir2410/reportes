<div class="col-md-2 " @click="loadMetricasKpi">

    <div class="info-box bg-yellow " v-if="abandoned != '-'">
      <span class="info-box-icon"><i class="fa fa-close"></i></span>

      <div class="info-box-content">
        <div class="info-box-content-centrado" >
            <span class="info-box-text2">Abandoned</span>
            <span class="info-box-number2">@{{ abandoned }}</span>
        </div>
      </div>
    </div>
    <div v-else>
        @include('layout.recursos.loading_bar')
    </div>

</div>
