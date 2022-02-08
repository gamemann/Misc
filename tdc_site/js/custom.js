// DataTables
var usersTable = null;
var serversTable = null;
var playersTable = null;

// Initalizes the Users DataTable.
function initUsersTable()
{
	usersTable = $('#userstable').DataTable({
		"lengthMenu": [30, 60, 90, 150, 200, 400],
		"order": [3, 'desc'],
		autoWidth: true,
		responsive: 
		{
			breakpoints: 
			[
				{ name: 'desktop', width: Infinity },
				{ name: 'smalldesktop', width: 1500 },
				{ name: 'tablet',  width: 1024 },
				{ name: 'fablet',  width: 768 },
				{ name: 'phone',   width: 480 }
			],
			details: false,
		},
		"columnDefs":
		[		
			// Steam Name
			{ className: "desktop smalldesktop tablet fablet phone", "targets": [ 0 ] },					
			
			// Steam ID
			{ className: "desktop smalldesktop", "targets": [ 1 ] },	
			
			// Group
			{ className: "desktop smalldesktop", "targets": [ 2 ] },	
			
			// Last Logged In
			{ className: "desktop", "targets": [ 3 ] },		
		],
	});
}

// Checks if the value is a number.
function checkIfNumber(e)
{
   var charCode = (e.which) ? e.which : event.keyCode
   
    if (charCode > 31 && (charCode < 48 || charCode > 57))
	{
        return false;
	}
	
    return true;
}

// Checks if the amount is enough.
function isEnough()
{
	var val = $('#amount').val();
	
	if (val > 4)
	{
		return true;
	}
	else
	{
		alert('Minimum you can donate is $5.00');
		return false;
	}
}

$(window).load(function()
{
	// When the amount is changed, update the days amount.
	$('#amount').on('input', function(e)
	{
		// So, $7.00 every 30 days. Now for the math!
		var amount = $(this).val();
		var factor = amount / 7;
		var days = Math.round(factor * 30);
		
		// Change the days amount.
		$('#daysamount').html('<span style="color: #647CFF;"><strong>' + days + '</strong></span> days of VIP.');
	});
});

// Initalizes the Game Servers table.
function initServersTable()
{
	serversTable = $('#serverstable').DataTable({
		"lengthMenu": [ 30, 60, 90, 150, 200, 400],
		"order": [3, 'desc'],
		autoWidth: true,
		responsive: 
		{
			breakpoints: 
			[
				{ name: 'desktop', width: Infinity },
				{ name: 'smalldesktop', width: 1500 },
				{ name: 'tablet',  width: 1024 },
				{ name: 'fablet',  width: 768 },
				{ name: 'phone',   width: 480 }
			],
			details: false,
		},
		"columnDefs":
		[		
			// Host Name
			{ className: "desktop smalldesktop tablet fablet phone", "targets": [ 0 ] },					
			
			// IP
			{ className: "desktop", "targets": [ 1 ] },	
			
			// Port
			{ className: "desktop", "targets": [ 2 ] },	
			
			// Players
			{ className: "desktop smalldesktop", "targets": [ 3 ] },				
			
			// MaxPlayers
			{ className: "desktop smalldesktop", "targets": [ 4 ] },				
			
			// Map
			{ className: "desktop", "targets": [ 5 ] },				
			
			// Connect
			{ className: "desktop smalldesktop tablet fablet phone", "targets": [ 6 ] },		
		],
	});
}

// Resize the tables.
$(window).resize(function()
{
	if (usersTable)
	{
		$('#userstable').css('width', '100%');
	}	
	
	if (serversTable)
	{
		$('#serverstable').css('width', '100%');
	}
	
	if (playersTable)
	{
		$('#playerstable').css('width', '100%');
	}
});

// Displays information about the user you are hover. Quite a bit of code.
var lock = false;
function pushCard(ele, id)
{
	if (lock)
	{
		return;
	}
	
	// First, we need to make sure the 
	if ($('#card-' + id))
	{
		// Simple, display the card.
		$('#card-' + id).css('display', 'block');
	}
	else
	{
		lock = true;
		// Not as simple. We actually need to create the card.
		$.post('/scripts/createCard', {
			userid: id,
		}, function (Data)
		{
			// When complete, we should display this and give it positions.
			if (Data != 0)
			{
				$(body).append(Data);
				
				$('#card-' + id).css('display', 'none');
				
				// Move the card.
				$('#card-' + id).css('left', pos.left);
				$('#card-' + id).css('top', pos.top - 3);
				
				lock = false;
			}
		});
	}
}

// Removes the card (display: none).
function pullCard(ele, id)
{
	// First, we need to make sure the 
	if ($('card-' + id))
	{
		$('card-' + id).css('display', 'none');
	}
}

// Initalizes the Players Table.
function initPlayersTable()
{
	playersTable = $('#playerstable').DataTable({
		"lengthMenu": [ 30, 60, 90, 150, 200, 400],
		"order": [1, 'desc'],
		autoWidth: true,
		responsive: 
		{
			breakpoints: 
			[
				{ name: 'desktop', width: Infinity },
				{ name: 'smalldesktop', width: 1500 },
				{ name: 'tablet',  width: 1024 },
				{ name: 'fablet',  width: 768 },
				{ name: 'phone',   width: 480 }
			],
			details: false,
		},
		"columnDefs":
		[		
			// Name
			{ className: "desktop smalldesktop tablet fablet phone", "targets": [ 0 ] },					
			
			// Frags
			{ className: "desktop smalldesktop tablet fablet", "targets": [ 1 ] },	
			
			// Time
			{ className: "desktop smalldesktop", "targets": [ 2 ] },		
		],
	});
}

// Saves an updated server.
function saveServer(id, type)
{
	if (type == 'admins')
	{
		// This will be fun >:D
		var adminIDs = [];
		$('.managementrow').each(function(key, val)
		{
			var newVal = $(this).find('.userid').val();
			
			if (newVal > 0 && newVal != '' && newVal.trim != '')
			{
				adminIDs.push(newVal);
			}
		});
		
		var toJSON = JSON.stringify(adminIDs);
		$.post('/pages/admin/servers/saveserver.php', {
			type: 'admins',
			serverID: id,
			admins: toJSON,
		}, function (Data)
		{
			if (Data == 1)
			{
				alert('Successfully saved admins!');
			}
			else
			{
				alert(Data);
			}
		});
	
	}
	else if (type == 'general')
	{
		$.post('/pages/admin/servers/saveserver.php', {
			type: 'general',
			serverID: id,
			ip: $('#server-ip').val(),
			publicip: $('#server-publicip').val(),
			port: $('#server-port').val(),
			game: $('#server-game').val(),
			location: $('#server-location').val(),
		}, function (Data)
		{
			if (Data == 1)
			{
				alert('Successfully saved server!');
			}
			else
			{
				alert(Data);
			}
		});
	}
}

// Adds an admin slot for managers to fill.
function addAdminSlot()
{
	$('#managementblocks').prepend('<div class="managementrow"><div class="form-group"><input type="text" class="form-control custom-control userid" size="2" placeholder="User ID..." style="width: 20%; margin: 0 auto;" /></div></div>');
}

// Checks a forum thread for the minimum length.
function meetTopicLength(title, content)
{
	var titleLen = $('#topic-title').val().length;
	var contentLen = $('#topic-content').val().length;
	
	// Check to see if they are just putting white spaces.
	var titleVal = $('#topic-title').val();
	var contentVal = $('#topic-content').val();
	
	if (contentVal.trim() == '' || titleVal.trim() == '')
	{
		alert ('Nice try...');
		return false;
	}
	
	if (titleLen >= title && contentLen >= content)
	{
		return true;
	}
	
	alert ('Did not exceed the minimum amount of characters.');
	
	return false;
}

// Checks the content of a reply to see if it meets the correct amount.
function meetReplyLength(content)
{
	var contentLen = $('#reply-content').val().length;
	
	// Check to see if they are just putting white spaces.
	var contentVal = $('#reply-content').val();
	
	if (contentVal.trim() == '')
	{
		alert ('Nice try...');
		return false;
	}
	
	if (contentLen >= content)
	{
		return true;
	}
	
	alert ('Did not exceed the minimum amount of characters.');
	
	return false;
}

// Adds a normal ChangeLog.
function addNormChangeLog()
{
	var toAppend = '<div class="changelog-detail"><div class="form-group"><div class="col-xs-1 col-sm-1 col-md-1 col-lg-1"><span class="glyphicon glyphicon-minus-sign" style="color: #FF6262; cursor: pointer; vertical-align: center;" onClick="removeChangeLog(this);"></span></div><div class="col-xs-11 col-sm-11 col-md-11 col-lg-11"><input type="text" class="normal-changelog form-control custom-control" placeholder="Changelog..." /></div></div></div>';
	
	$('#changelog-content').append(toAppend);
}

// Adds a category changelog.
function addCategoryChangeLog()
{
	var toAppend = '<div class="changelog-category"><div class="row"><div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xs-offset-3 col-sm-offset-3 col-md-offset-3 col-lg-offset-3"><div class="form-group"><input type="text" class="form-control custom-control changelog-category-title" placeholder="Title..." /></div></div></div><div class="changelog-category-details"></div><div class="text-center"><span class="glyphicon glyphicon-plus-sign" style="color: #34CF0E; font-size: 150%; cursor: pointer; padding-right: 30px;" onClick="addDetailChangeLog(this);"></span><span class="glyphicon glyphicon-minus-sign" style="color: #FF6262; font-size: 150%; cursor: pointer; padding-right: 30px;" onClick="removeCategoryChangeLog(this);"></span></div></div>';
	
	$('#changelog-content').append(toAppend);
}

// Removes a normal changelog.
function removeChangeLog(ele)
{
	$(ele).parent().parent().parent().remove();
}

// Removes a category changelog.
function removeCategoryChangeLog(ele)
{
	$(ele).closest('.changelog-category').remove()
}

// Adds a category changelog.
function addDetailChangeLog(myParent)
{
	var toAppend = '<div class="category-detail"><div class="form-group"><div class="col-xs-1 col-sm-1 col-md-1 col-lg-1"><span class="glyphicon glyphicon-minus-sign" style="color: #FF6262; cursor: pointer; vertical-align: center;" onClick="removeCategoryDetail(this);"></span></div><div class="col-xs-11 col-sm-11 col-md-11 col-lg-11"><input type="text" class="normal-changelog form-control custom-control" placeholder="Changelog..." /></div></div></div>';
	
	$(myParent).parent().siblings('.changelog-category-details').append(toAppend);
}

// Removes category detail.
function removeCategoryDetail(myParent)
{
	$(myParent).closest('.category-detail').remove();
}

// Adds the change-log.
function addChangeLog()
{
	// Well, this is going to be fun.
	var iServer = $('#changelog-server').val();
	var sType = $('#changelog-type').val();
	
	var arrItems = new Array();
	
	// Loop through all the items.
	
	// Normal Details.
	$('#changelog-content .changelog-detail').each(function(i, ele)
	{
		var val = $(this).find('.normal-changelog').val();
		
		arrItems.push(val);
	});
	
	// Categories.
	$('#changelog-content .changelog-category').each(function(i, ele)
	{
		
		var tempArray = [];
		var sTitle = $(this).find('.changelog-category-title').val();
		
		$(this).find('.category-detail').each(function(ii, elee)
		{
			var val = $(this).find('.normal-changelog').val();
			tempArray.push(val);
		});
		var newArray = {"Title": sTitle, "Details": tempArray};
		arrItems.push(newArray);
	});
	
	$.post('/pages/admin/servers/submit-changelog.php', {
		type: sType,
		serverID: iServer,
		items: JSON.stringify(arrItems)
	}, function (Data)
	{
		if (Data == 1)
		{
			alert("Change-Log successfully inserted!");
		}
		else
		{
			alert(Data);
		}
	});
	
}