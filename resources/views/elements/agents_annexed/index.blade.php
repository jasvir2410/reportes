<div class="panel-body">
	<div class="panel panel-primary">
		<ul class="nav nav-tabs nav-pills ">
			<li role="tab" class="active">
				<a href="#panel-report" role="tab" data-toggle="tab" onclick="show_tab_annexed('free')">
					<icon class="glyphicon glyphicon-earphone"></icon>Free
				</a>
			</li>
			<li role="tab">
				<a href="#panel-report" role="tab" data-toggle="tab" onclick="show_tab_annexed('user')">
					<icon class="fa fa-users"></icon>Users
				</a>
			</li>
			<li role="tab">
				<a href="#panel-report" role="tab" data-toggle="tab" onclick="show_tab_annexed('backoffice')">
					<icon class="fa fa-briefcase"></icon>BackOffice
				</a>
			</li>
		</ul>
		<div class="panel-body" id="divListAnnexed">
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		show_tab_annexed('free')
	})
</script>
