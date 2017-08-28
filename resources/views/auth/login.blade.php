@extends('layout.login')
@section('title', 'Login Agente')
@section('content')
<div class="container">
  @include('layout.recursos.error')
  <div class="card card-container" style="background-color: transparent">
    <center><img id="profile-img" class="profile-img-card" src="img/logo.svg" /></center>
    <br>
    <p id="profile-name" class="profile-name-card"></p>
    <form role="form" method="POST" action="{{route('login')}}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">

      <div class="input-group input-group-sm">
        <span class="input-group-addon" id="sizing-addon3"><i class="fa fa-user"></i></span>
        <input type="text" class="form-control" placeholder="Ingrese su Usuario" aria-describedby="sizing-addon3" id="inputUsername" required="autofocus" name="username">
      </div>
      <div class="input-group input-group-sm">
        <span class="input-group-addon" id="sizing-addon3"><i class="fa fa-lock"></i></span>
        <input type="password" class="form-control" placeholder="Ingrese su Contraseña" aria-describedby="sizing-addon3" id="inputPassword" required="required" name="password">
      </div>
      <br>
      <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Ingresar</button>
    </form>
  </div><!-- /card-container -->
</div><!-- /container -->
@endsection
