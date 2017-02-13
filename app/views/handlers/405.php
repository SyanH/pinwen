<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>405 Method not allowed</title>
		<style>
		body
		{
			height:100%;
			background:#eee;
			padding:0px;
			margin:0px;
			height: 100%;
			font-size: 100%;
			color:#333;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			line-height: 100%;
		}
		h1
		{
			font-size: 4em;
		}
		small
		{
			font-size: 0.7em;
			color: #999;
			font-weight: normal;
		}
		hr
		{
			border:0px;
			border-bottom:1px #ddd solid;
		}
		.message
		{
			width: 700px;
			margin: 15% auto;
		}
		</style>
	</head>
	<body>

		<div class="message">
			<h1>405 <small>Method not allowed</small></h1>
			<hr>
			<p>Method not allowed. Must be one of: <strong><?php echo $allow; ?></strong></p>
		</div>

	</body>
</html>