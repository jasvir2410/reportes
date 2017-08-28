<div class="row-fluid" id='detailAgents'>
    <div class="box box-primary box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">Filter Dashboard</h3>
        </div>
        <div class="box-body">
            <div class="col-md-12">
                <v-select multiple :value.sync="roleDefault" :on-change="loadRolePermission" :options="['Admin','Supervisor','Backoffice','Calidad','Cliente','User']" placeholder="Choose Role Here !"></v-select>
            </div>
        </div>
    </div>
</div>