<div class="col-md-2 " id="total_encoladas" v-if="totalCallsWaiting != 0">
    <div class="info-box bg-yellow">
		<span class="info-box-icon2">
			<i class="fa fa-sign-out"></i>
		</span>
		<div class="info-box-content2" @click="loadCallWaiting">
			<span class="info-box-text2">Encoladas</span>
			<span class="info-box-number2" id="count_encoladas">@{{ totalCallsWaiting }}</span>
		</div>
	</div>
</div>
