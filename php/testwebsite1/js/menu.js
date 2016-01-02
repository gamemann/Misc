var bMMO = false; // Mobile Menu Opened

function OpenMobileMenu()
{
	if (!bMMO)
	{
		var i = document.getElementsByClassName("mobilemenu");
		i[0].style.display = "block";
		
		bMMO = true;
	}
	else
	{
		var i = document.getElementsByClassName("mobilemenu");
		i[0].style.display = "none";
		
		bMMO = false;
	}
}