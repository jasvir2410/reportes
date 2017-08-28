<header class="main-header">

    <!-- Logo -->
    <a href="/" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">{{getenv('PROYECT_NAME_SHORT')}}</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">{{getenv('PROYECT_NAME_COMPLETE')}}</span>
    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation" id="menuHeader">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li v-show="annexed != 0" @click="liberarAnexos">
                    <h5 class="navbar-text navbar-txt2" data-toggle="tooltip" data-placement="bottom" title="Anexo Actual" style="color: white">
                      <span class="fa fa-headphones"></span> Anexo: <font >@{{ annexed}}</font>
                    </h5>
                </li>
                <li v-show="textDateServer.length != 0">
                    <!-- Fecha Actual -->
                    <h5 id="fecha_actual" class="navbar-text navbar-txt2" data-toggle="tooltip" data-placement="bottom" title="Fecha Actual" style="color: white">
                        <span class="glyphicon glyphicon-calendar"> @{{ textDateServer }}</span>
                    </h5>
                </li>
                <li v-show="hourServer.length != 0">
                    <!-- Hora Actual -->
                    <h5 id="hora_actual"  class="navbar-text navbar-txt2" data-toggle="tooltip" data-placement="bottom" title="Hora Actual" style="color: white">
                        <span class="glyphicon glyphicon-time"> @{{ hourServer }}</span>
                    </h5>
                </li>
                <li>
                </li>
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img :src="'storage/' + srcAvatar" class="user-image" alt="User Image">
                        <span class="hidden-xs">Sapia</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img :src="'storage/' + srcAvatar" class="img-circle" alt="User Image">
                            <p>
                                @{{ getNameComplete + ' - ' }}<font id="UserNameRole">@{{getRole}}</font>
                                <small>{{date('j F Y')}}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat reportes" id="profile_users" >Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" onClick="javascript:disconnect()" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
