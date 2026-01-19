<!DOCTYPE html>
<html lang="en">
<head>
	<title>Crystal Tools - {{ type }}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0">
	<style>code{padding:2px 4px;color:#d14;}</style>
	<link href="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Style/fonts.css" type="text/css" rel="stylesheet"  />
	<link href="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Style/index.css" type="text/css" rel="stylesheet"  />
	
	<style>
	.details-list{
		text-align: left;
		margin-left: 0px;
		margin-top: 10px;
	}
	
	.details-list li{
		font-family: monospace;
		font-size: 12px;
	}
	</style>

	<!--[if lt IE 9]>
		<script type="text/javascript" src="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Script/html5.js" ></script>
	<![endif]-->
	
</head>
<body>
	<div class="row-fluid" id="exception-wrapper">
		<div class="slogan-1">{{ type }} </div>
		
		<h2>Exception Details</h2>
		<table class="special">
			<tr>
				<th>Message</th>
				<td><center>{{ message|raw }}</center></td>
			</tr>
			{% if details %}

			<tr>
				<th>Details</th>
				<td style="text-align: center">
				<ol class="details-list">
				{% for detail in details %}
					<li>{{ detail }}</li>
				{% endfor %}
				</ol>
				</td>
			</tr>
			{% endif %}
		</table>
		
		<h2>Stack Trace</h2>
		<table class="special">
			<tr>
				<td><pre>{{ trace }}</pre></td>
			</tr>
		</table>
		
		{% for global_param in global %}
		<div class="block">
		<h2>Request {{ _key }} Parameters</h2>
			<table class="special">
			{% for param in global_param %}
				<tr>
					<th>{{ _key }}</th><td>{{ param }}</td>
				</tr>
			{% else %}
				<i>No {{ _key }} Parameters</i>
			{% endfor %}
			</table>
		</div>
		{% endfor %}
	</div>
</body>

</html>

