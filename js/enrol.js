(function(){
	var users = $("#addselect option"),
		enrolled_users = $("#removeselect option"),
		waiting_users = $("#waitingselect option"),
		$enrol_info = $('#enrol_info'),
		$enrolled = $enrol_info.find('tr:eq(1) .c1'),
		$waiting = $enrol_info.find('tr:eq(2) .c1');

	// Make sure you cannot select enrolled and waiting users at the same time
	$("#removeselect").change(function() {
		document.getElementById('waitingselect').selectedIndex = -1;
	});
	$("#waitingselect").change(function() {
		document.getElementById('removeselect').selectedIndex = -1;
	});
	
	// add input search
	$("#addselect_searchtext").on("change keyup", function(){
	
		var filter = $("#addselect_searchtext").val();
		if (filter === "") {
			$("#addselect option").remove();
			users.appendTo("#addselect");
		} else {
			var filtered_users = [];
			for (var i = users.length - 1; i >= 0; i--) {
				if (users[i].innerText.toLowerCase().indexOf(filter.toLowerCase()) !== -1) {
					// users.splice(i, 1);
					filtered_users.push(users[i]);
				}
			}

			$("#addselect option").remove();
			$("#addselect").append($(filtered_users));
		}
		
	});

	// remove input search
	$("#removeselect_searchtext").on("change keyup", function(){
	
		var filter = $("#removeselect_searchtext").val();
		if (filter === "") {
			$("#removeselect option").remove();
			enrolled_users.appendTo("#removeselect");
		} else {
			var filtered_users = [];
			for (var i = enrolled_users.length - 1; i >= 0; i--) {
				if (enrolled_users[i].innerText.toLowerCase().indexOf(filter.toLowerCase()) !== -1) {
					// users.splice(i, 1);
					filtered_users.push(enrolled_users[i]);
				}
			}

			$("#removeselect option").remove();
			$("#removeselect").append($(filtered_users));
		}
	});

	$("#add").on('click', function(e){
		e.preventDefault();
		var courseid = $(this).siblings("#courseID").val();
		var userids = $("#addselect :selected");
		var sendEmail = $('#sendEmail').prop('checked');
		var user_role = $('#enrol_role :selected').val();

		var spinner = M.util.add_spinner(Y, Y.one('#addcontrols'));
		spinner.show();

		userids.each(function(k, v){
			var uid = $(v).val();
			$.ajax({
				type: "POST", 
				url: "./api.php",
				dataType: 'json',
				data: { 	
					enrolGuy: uid,
					enrolCourse: courseid, 
					sendEmail: sendEmail,
					enrolRole: user_role
				},
				success: function (response) {
					users = users.filter(function (i, value) {
						return $(value).val() !== uid;
					});

					if(response.status === "waitlist"){
						$waiting.html(parseInt($waiting.html(), 10) + 1);
						$(v).appendTo("#waitingselect");
						waiting_users.push(v);
					} else {
						$enrolled.html(parseInt($enrolled.html(), 10) + 1);
						$(v).appendTo("#removeselect");
						enrolled_users.push(v);
					}

					spinner.hide();
				},
				error: function () {
					spinner.hide();
				}
			});
		});

		return false;
	});

	$("#remove").on('click', function(e){
		e.preventDefault();
		var courseid = $(this).siblings("#courseID").val();
		var userids = $("#removeselect :selected").add($('#waitingselect :selected'));

		var spinner = M.util.add_spinner(Y, Y.one('#removecontrols'));
		spinner.show();

		userids.each(function(k, v){
			var uid = $(v).val();
			$.ajax({
				type: "POST", 
				url: "./api.php",
				data: { 	
					unenrolGuy: uid,
					enrolCourse: courseid 
				},
				success: function() {
					var $v = $(v);

					if ($v.parent('#removeselect').length) {
						// User was previously enrolled
						enrolled_users = enrolled_users.filter(function (i, value) {
							return $(value).val() !== uid;
						});
						users.push(v);

						// Move one user from waiting list
						if (waiting_users.length) {
							$waiting.html(parseInt($waiting.html(), 10) + 1);

							var mover = waiting_users.first();
							waiting_users = waiting_users.not(mover);
							mover.appendTo('#removeselect');
							enrolled_users.add(mover);
						} else {
							$enrolled.html(parseInt($enrolled.html(), 10) - 1);
						}
					} else {
						// User was previously on waiting list
						$waiting.html(parseInt($waiting.html(), 10) - 1);

						waiting_users = waiting_users.filter(function (i, value) {
							return $(value).val() !== uid;
						});
						users.push(v);
					}

					$v.appendTo("#addselect");


					spinner.hide();
				},
				error: function () {
					spinner.hide();
				}
			});
		});
		// location.reload();
		return false;
	});

	$("#addselect_clearbutton").on("click",function(){
		$("#addselect_searchtext").val("");
		$( "#addselect_searchtext" ).trigger( "keyup" );
	});

	$("#removeselect_clearbutton").on("click",function(){
		$("#removeselect_searchtext").val("");
		$( "#removeselect_searchtext" ).trigger( "keyup" );
	});
})();
