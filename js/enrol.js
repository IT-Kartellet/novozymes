(function(){
	var users = $("#addselect option");
	var enrolled_users = $("#removeselect option");
	
	// add input search
	$("#addselect_searchtext").on("change keyup", function(){
	
		var filter = $("#addselect_searchtext").val();
		if (filter == "") {
			$("#addselect option").remove();
			users.appendTo("#addselect");
		} else {
			var filtered_users = [];
			for (var i = users.length - 1; i >= 0; i--) {
				if (users[i].innerText.toLowerCase().indexOf(filter.toLowerCase()) != -1) {
					// users.splice(i, 1);
					filtered_users.push(users[i]);
				};
			};	

			$("#addselect option").remove();
			$("#addselect").append($(filtered_users));
		}
		
	});

	// remove input search
	$("#removeselect_searchtext").on("change keyup", function(){
	
		var filter = $("#removeselect_searchtext").val();
		if (filter == "") {
			$("#removeselect option").remove();
			enrolled_users.appendTo("#removeselect");
		} else {
			var filtered_users = [];
			for (var i = enrolled_users.length - 1; i >= 0; i--) {
				if (enrolled_users[i].innerText.toLowerCase().indexOf(filter.toLowerCase()) != -1) {
					// users.splice(i, 1);
					filtered_users.push(enrolled_users[i]);
				};
			};

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
		//console.log(user_role);
		userids.each(function(k, v){
			var uid = $(v).val();
			$.ajax({
			  type: "POST", 
			  url: "./api.php",
			  data: { 	
			  			enrolGuy: uid,
			  			enrolCourse: courseid, 
			  			sendEmail: sendEmail,
			  			enrolRole: user_role
			  		},
			  success: function(e){
				if(e == "full"){
					//alert("Course is full and user is not enrolled");
					//location.reload();
				}
			  	//console.log("SUCCESS:", e);
			  	$(v).appendTo("#removeselect");
				users = users.filter(function (i, value) {
					return $(value).val() !== uid;
				});
				enrolled_users.push(v);
			  },
			  error: function(e){
			  	// console.log("ERROR:", e);
			  }
			});
			 //console.log("user:", uid, "course:", courseid);
		});
		// location.reload();
		return false;
	});

	$("#remove").on('click', function(e){
		e.preventDefault();
		var courseid = $(this).siblings("#courseID").val();
		var userids = $("#removeselect :selected");
		userids.each(function(k, v){
			var uid = $(v).val();
			$.ajax({
			  type: "POST", 
			  url: "./api.php",
			  data: { 	
			  			unenrolGuy: uid,
			  			enrolCourse: courseid 
			  		},
			  success: function(e){
			  	// console.log("uid:", uid, "courseid:", courseid, "---", e);
			  	$(v).appendTo("#addselect");
				enrolled_users = enrolled_users.filter(function (i, value) {
					return $(value).val() !== uid;
				});
				users.push(v);
			  },
			  error: function(e){
			  	// console.log("uid:", uid, "courseid:", courseid, "---", e);
			  }
			});
			// console.log("user:", uid, "course:", courseid);
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
