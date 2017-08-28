<div class="col-md-2" @click="loadMetricasKpi">
    <div class="info-box bg-green" v-if="answeredTime != '-'">
		<span class="info-box-icon2">
			<i class="fa fa-sign-out"></i>
		</span>
        <div class="info-box-content2" >
            <span class="info-box-text2">Answered @{{ answeredSymbol }} @{{ answeredSecond }} Seg</span>
            <span class="info-box-number2">@{{ answeredTime }}</span>
        </div>
    </div>
    <div v-else>
        @include('layout.recursos.loading_bar')
    </div>
</div>
