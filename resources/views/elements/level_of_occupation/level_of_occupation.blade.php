<input type="hidden" name="_token" value="{!! csrf_token() !!}">

<div class="panel panel-default">
    <div class="panel-body" >
        <div class="tab-pane fade active in" id="cuerpo1">
            <table id="table-level-occupation" class="table table-bordered display nowrap table-responsive" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Indbound</th>
                    <th>ACW</th>
                    <th>Outbound</th>
                    <th>Auxiliares</th>
                    <th>Logueo</th>
                    <th>Nivel Ocupacion</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        buscar()
    })

    function buscar(){
        show_tab_level_occupation('level_of_occupation')
    }
</script>