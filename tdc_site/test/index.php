<html>
	<head>
		<script src="../js/jquery-1.11.3.min.js"></script>
		<script>
			$(window).load(function()
			{
				$('#mytext').keyup(function()
				{
					var text = '';
					$.post('linebreak.php', {text: $('#mytext').val()}, function(Data)
					{
						text = Data;
						$('#currentText').html(text);
					});
				});
			});
		</script>
		<style type="text/css">
			#bar
			{
				position: absolute;
				z-index: 2;
				top: 0px;
				left: 100%;
			}
		</style>
	</head>
	
	<body>
		<div id="bar">Open</div>
		<div id="currentText" style="background-color: #EEEEEE; border: 1px solid #000000; padding: 15px; color: #000000;">
			
		</div>
		
		<br />
		Text: <br />
		<textarea id="mytext"></textarea>
	</body>
	
</html>