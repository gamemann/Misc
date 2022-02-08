// For stress testing
function stress()
{
	$.get('stressfile.php', function (Data)
	{
		console.log(Data);
	});
}