<div class="row text-center" >
    <div class="col-xs-12">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                @foreach ($tabla_anexos as $key => $tabla_anexo)
                    <tr>
                        @foreach ($tabla_anexo as $key => $anexo)
                            <td>
                                <center>
                                    <a href="#"  class='{{ $anexo['btn']  }}' @click="asignAnnexed">

                                    <a href="#"  class='{{ $anexo['btn']  }}' onclick="assignAnexxed('{{ $anexo['name'] }}','{{$anexo['user']['id']}}','{{$anexo['user']['username']}}');" >

                                        <center>
                                            <img class="img-responsive" width="74" height="74" name={{ $anexo['name'] }}  src={{ asset('img/'.$anexo['image']) }}  />
                                        </center>
                                        <center>
										    <span style='color:black'>
											    Anexo {{ $anexo['name'] }}
                                                <br>
                                                @if($anexo['user']['id'] != '')
                                                    <font style="color:#088A29;">{{$anexo['user']['primer_nombre']}} {{$anexo['user']['apellido_paterno']}}</font>
                                                @else
                                                    <font style="color:#088A29;"> - </font>
                                                @endif
											</span>
                                        </center>
                                    </a>
                                </center>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>