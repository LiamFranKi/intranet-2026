<!DOCTYPE html>
<html lang="en">
<head>
	<title>Crystal Tools - {{ end(explode('\\', type)) }}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0">
	<style>code{padding:2px 4px;color:#d14;}</style>
	<link href="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Style/fonts.css" type="text/css" rel="stylesheet"  />
	<link href="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Vendor/Bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet"  />
	<link href="{{ INSTALL_DIRECTORY }}/Core/Administration/Static/Style/index.css" type="text/css" rel="stylesheet"  />
	
	<style>
	.details-list{
		text-align: left;
		margin-left: 40px;
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
		<div class="slogan-1">{{ end(explode('\\', type)) }} </div>
		
		<h2>Exception Details</h2>
		<table class="special">
			<tr>
				
				<td><center>An exception has thrown, sorry for the inconveniences</center></td>
			</tr>
			
			<tr>
				<td colspan="2" class="center">
					<a href="/" class="btn"><i class="icon-home"></i> Go Home</a>
					<a href="javascript:history.back(-1);" class="btn"><i class="icon-arrow-left"></i> Go back</a>
				</td>
			</tr>
		</table>
		

	</div>
</body>

</html>

