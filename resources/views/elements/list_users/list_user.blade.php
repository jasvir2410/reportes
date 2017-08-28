<input type="hidden" name="_token" value="{!! csrf_token() !!}">

<div class="panel panel-default">
    <div class="panel-body" >
        <div class="box-body">
            <div class="form-group col-md-5">
                <div class="input-group">
                    <a onclick="createUser();" class="btn btn-primary"><i class="fa fa-user-plus"></i> Agregar Usuario</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade active in" id="panel-report">
            <table id="table-list-user" class="table table-bordered display nowrap table-responsive" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>First Name</th>
                    <th>Second Name</th>
                    <th>Last Name</th>
                    <th>Second Last Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Estado</th>
                    <th>Change Rol</th>
                    <th>Change Password</th>
                    <th>Change Status</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        buscar();
    })
    function buscar(){
        show_tab_list_user('list_users')
        //DataTableHide('table-list-user',[0,8,9],'{{Session::get('UserRole')}}')
    }
</script>