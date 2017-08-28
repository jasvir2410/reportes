{!! Form::open([ 'method' => '', 'name'=>'buscador']) !!}
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><b>@yield('titleReport')</b></h3>
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		</div>
	</div>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	<input type="hidden" id="hidEvent" value="{{$exportReport}}">
	<input type="hidden" id="hidDefaultEvent" value="{{$nameRouteController}}">
	<div class="box-body" style="display: block;">
		<div class="row">

			<div class="col-md-4">
				<!-- Date range -->
				<div class="box-body">
					<div class="input-group">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						@if($viewDateSearch == true)
							<input type="text" id="texto" name="fecha_evento" class="form-control pull-right" >
						@endif
						@if($viewDateSingleSearch == true)
							<input type="text" id="texto" name="fecha_evento" class="form-control fecha_evento_single pull-right" >
						@endif
					</div>
				</div>
			</div>

			@if($viewHourSearch == true)
				<div class="col-md-2">
					<!-- Date range -->
					<div class="box-body">
						<div class="input-group">
							<div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</div>
							<select class="form-control" name="rankHour" id="rankHour">
								<option value="1800">30 minutos</option>
								<option value="3600">1 hora</option>
							</select>
						</div>
					</div>
				</div>
			@else
				<div class="col-md-2"></div>
			@endif


			<div class="col-md-6">
				<div class="box-body">
					@if($viewButtonSearch == true)
						<div class="col-md-6"><a onclick="buscar();"  class="btn btn-primary"><span class="fa fa-search" aria-hidden="true"></span> Search</a></div>
					@else
						<div class="col-md-6"></div>
					@endif
					<div class="col-md-3"><a onclick="exportar('csv');" class="btn btn-success"><span class="fa fa-file-code-o" aria-hidden="true"></span> Export Csv</a></div>
					<div class="col-md-3"><a onclick="exportar('excel');" class="btn btn-success"><span class="fa fa-file-excel-o" aria-hidden="true"></span> Export Excel</a></div>
				</div>
			</div>
		</div>
	</div>
</div>
{!! Form::close() !!}

@yield('routeReport')
