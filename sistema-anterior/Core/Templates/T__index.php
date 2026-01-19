<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{{ title }}</title>
		<!-- jquery -->
		{{ js('jquery-1.7.1.min') }}
		<!-- assets -->
		{{ css('index') }}
		{{ js('index') }}
	</head>
	<body>
	{{ block('content') }}
	</body>
</html>
