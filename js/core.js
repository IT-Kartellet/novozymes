(function(){

	var enrolledCourse;
	var calendarInput = $('input[name*=calendar]');
	calendarInput.removeClass('visibleifjs');
	calendarInput.addClass('ninja');
	try {
		$("select[name*=target]").select2({
			placeholder: "Select target",

		});

		$("select[name*=target]").on('change', function(e){
			
		});
	} catch (err){
	}
	
	if ($('#id_multipledates').is(":checked")) {
		$("#fitem_id_multiple_dates").show();
	} else {
		$("#fitem_id_multiple_dates").hide();
	}

	if ($('#id_customemail').is(":checked")) {
		$("#[id^='fitem_id_custom_email']").show();
	} else {
		$("[id^='fitem_id_custom_email']").hide();
	}
	
	
	$(document.body).on("click","#addDateCourse",function(){

		//used to duplicate the datecourses;

		var course = $("div.template").last().clone();
		$("#wrapper").append(course);
		var victim = $(".template").last();
		var index = $('.template').length;

		victim.find('input').val("");
		victim.find('select').val("0");
		victim.find("select[name='timestart[" + (index-2) + "][day]']").attr("name", "timestart[" + (index-1) + "][day]");
		victim.find("select[name='timestart[" + (index-2) + "][month]']").attr("name", "timestart[" + (index-1) + "][month]");
		victim.find("select[name='timestart[" + (index-2) + "][year]']").attr("name", "timestart[" + (index-1) + "][year]")
		victim.find("select[name='timestart[" + (index-2) + "][hour]']").attr("name", "timestart[" + (index-1) + "][hour]")
		victim.find("select[name='timestart[" + (index-2) + "][minute]']").attr("name", "timestart[" + (index-1) + "][minute]")


		victim.find("select[name='timeend[" + (index-2) + "][day]']").attr("name", "timeend[" + (index-1) + "][day]");
		victim.find("select[name='timeend[" + (index-2) + "][month]']").attr("name", "timeend[" + (index-1) + "][month]");
		victim.find("select[name='timeend[" + (index-2) + "][year]']").attr("name", "timeend[" + (index-1) + "][year]");
		victim.find("select[name='timeend[" + (index-2) + "][hour]']").attr("name", "timeend[" + (index-1) + "][hour]");
		victim.find("select[name='timeend[" + (index-2) + "][minute]']").attr("name", "timeend[" + (index-1) + "][minute]");

		victim.find("select[name='publishdate[" + (index-2) + "][day]']").attr("name", "publishdate[" + (index-1) + "][day]");
		victim.find("select[name='publishdate[" + (index-2) + "][month]']").attr("name", "publishdate[" + (index-1) + "][month]");
		victim.find("select[name='publishdate[" + (index-2) + "][year]']").attr("name", "publishdate[" + (index-1) + "][year]");
		victim.find("select[name='publishdate[" + (index-2) + "][hour]']").attr("name", "publishdate[" + (index-1) + "][hour]");
		victim.find("select[name='publishdate[" + (index-2) + "][minute]']").attr("name", "publishdate[" + (index-1) + "][minute]");

		victim.find("select[name='datecourse[" + (index-2) + "][location]']").attr("name", "datecourse[" + (index-1) + "][location]");
		victim.find("select[name='datecourse[" + (index-2) + "][language]']").attr("name", "datecourse[" + (index-1) + "][language]");
		victim.find("select[name='datecourse[" + (index-2) + "][coordinator]']").attr("name", "datecourse[" + (index-1) + "][coordinator]");


		victim.find("input[name='datecourse[" + (index-2) + "][price]']").attr("name", "datecourse[" + (index-1) + "][price]");
		victim.find("input[name='datecourse[" + (index-2) + "][id]']").attr("name", "datecourse[" + (index-1) + "][id]");
		victim.find("input[name='datecourse[" + (index-2) + "][places]']").attr("name", "datecourse[" + (index-1) + "][places]");
		victim.find("select[name='datecourse[" + (index-2) + "][category]']").attr("name", "datecourse[" + (index-1) + "][category]");
		victim.find("select[name='datecourse[" + (index-2) + "][currency]']").attr("name", "datecourse[" + (index-1) + "][currency]");

		victim.find("input[name='timestart[" + (index-2) + "][calendar]']").attr("name", "timestart[" + (index-1) + "][calendar]");
		victim.find("input[name='timend[" + (index-2) + "][calendar]']").attr("name", "timeend[" + (index-1) + "][calendar]");
	});

	$(document.body).on("click","#removeDateCourse",function(){

		$("div.template").last().remove();
		
	});


	// modal window for the TOS dialog
	$(document.body).on('click','input[value="Add me to waiting list"]',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background").show();
		$("#waitingSpan").show();
		if (!$('#lean_background input[name="accept"]').is(":checked")) {
			$('#lean_background input[name="submit"]').prop('disabled',true);
		};
	});

	//enrol me
	$(document.body).on('click','div.enrolMeButton input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background").show();
		$("#waitingSpan").hide();
		if (!$('#lean_background input[name="accept"]').is(":checked")) {
			$('#lean_background input[name="submit"]').prop('disabled',true);
		};
	});

	$(document.body).on('click','#accept_enrol', function(e){
		enrolledCourse.closest('form').submit();
	});

	$(document.body).on('click','#lean_background input[name="accept"]', function(){

		if ($('#lean_background input[name="submit"]').is(":disabled")) {
			$('#lean_background input[name="submit"]').prop('disabled',false);
		} else {
			$('#lean_background input[name="submit"]').prop('disabled',true);
		}
		
	});

	$(document.body).on('click','#lean_background input[name="cancel"]', function(){
		$('#lean_background').hide();
		$('#lean_background input[name="accept"]').prop('checked',false);
		$('#lean_background input[name="submit"]').prop('disabled',true);
	});


	//unenrol me
	$(document.body).on('click','div.unEnrolMeButton input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background_unenrol").show();
		$("#waitingSpan").hide();
		if (!$('#lean_background_unenrol input[name="accept_unenrol"]').is(":checked")) {
			$('#lean_background_unenrol input[name="submit"]').prop('disabled',true);
		}
	});

	$(document.body).on('click','#lean_background_unenrol input[name="accept_unenrol"]', function(){
		if ($('#lean_background_unenrol input[name="submit"]').is(":disabled")) {
			$('#lean_background_unenrol input[name="submit"]').prop('disabled',false);
		} else {
			$('#lean_background_unenrol input[name="submit"]').prop('disabled',true);
		}
		
	});

	$(document.body).on('click','#accept_unenrol', function(e){
		enrolledCourse.closest('form').submit();
	});


	$(document.body).on('click','#lean_background_unenrol input[name="cancel"]', function(){
		$('#lean_background_unenrol').hide();
		$('#lean_background_unenrol input[name="accept_unenrol"]').prop('checked',false);
		$('#lean_background_unenrol input[name="submit"]').prop('disabled',true);
	});

	$(document.body).on('click','#id_multipledates', function(){
		if ($('#id_multipledates').is(":checked")) {
			$("#fitem_id_multiple_dates").show();
		} else {
			$("#fitem_id_multiple_dates").hide();
		}
	});

	$(document.body).on('click','#id_customemail', function(){
		if ($('#id_customemail').is(":checked")) {
			$("[id^='fitem_id_custom_email']").show();
		} else {
			$("[id^='fitem_id_custom_email']").hide();
		}
	});

	//location handling
	// on settings form
	$('input[name="addLoc"]').on('click',function(e){
		e.preventDefault();
		var newLoc = $('input[name="addLocation"]').val();
		$('input[name="addLocation"]').val("");

		// add the location
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { newLocation: newLoc }
		})
		  .done(function( msg ) {
		    if (msg) {
		    	//get all the locations and update the list
		    	$.ajax({
				  type: "POST",
				  url: "./api.php",
				  data: { getLocations: 1 }
				})
					.done(function(locations){
						// remove all the locations, and draw them again.
						$("select[name='locations'] > option").remove();
						locations = $.parseJSON(locations);
						$.each(locations, function(k, v){
						    $("select[name='locations']").append($("<option value= '" + v.id + "'>" + v.location + "</option>"));
						});
					});
		    };
		  });
	});

	$('#allowHim').on('click',function(e){
		e.preventDefault();
		var newGuy = $("#cantenroll").find(":selected");
		var newGuyValue = newGuy.val();
		newGuy.appendTo("#canenroll");
		
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { newAllow: newGuyValue },
		  success: function(e){
		  	console.log(e);
		  },
		  error: function(e){
		  	console.log(e);
		  }
		})
	});

	$('#removeHim').on('click',function(e){
		e.preventDefault();
		var newGuy = $("#canenroll").find(":selected");
		var newGuyValue = newGuy.val();
		newGuy.remove();
		newGuy.appendTo("#cantenroll");
		
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { removeAllow: newGuyValue },
		  success: function(e){
		  	console.log(e);
		  },
		  error: function(e){
		  	console.log(e);
		  }
		})
	});

	$('#enrolHim').on('click',function(e){
		e.preventDefault();
		var newGuy = $("#icanenrol").find(":selected");
		var courseID = $("#courseID").val();
		var newGuyValue = newGuy.val();
		
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { enrolGuy: newGuyValue,
		  		enrolCourse: courseID },
		  success: function(e){
		  	newGuy.remove();
		  	console.log(e);
		  },
		  error: function(e){
		  	console.log(e);
		  }
		})
	});

	$('input[name="deleteLoc"]').on('click',function(e){
		e.preventDefault();
		var loc = $("select[name='locations']").find(":selected").val();

		// delete the location
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { deleteLocation: loc }
		})
			.done(function(locations){
				// remove all the locations, and draw them again.
				$("select[name='locations'] > option").remove();
				locations = $.parseJSON(locations);
				$.each(locations, function(k, v){
				    $("select[name='locations']").append($("<option value= '" + v.id + "'>" + v.location + "</option>"));
				});
			});
		   
	});

	// on the datecourse form
	$(document.body).on('click','.anotherLocation' ,function(e){
		console.log("asdas");
		e.preventDefault();
		window.scrollTo(0, 0);
		console.log("ni");
		$("#lean_background").show();

		$('input[name="addL"]').on('click',function(e){
			e.preventDefault();
			var newLoc = $('input[name="newLeanLocation"]').val();
			$('input[name="newLeanLocation"]').val("");

			// add the location
			$.ajax({
			  type: "POST", 
			  url: "./api.php",
			  data: { newLocation: newLoc }
			})
			  .done(function( loc ) {
			    if (loc) {
			    	var locat = $.parseJSON(loc);
			    	$.each(locat, function(k, v){
					    $("select[name*=location]").append($("<option value= '" + v.id + "'>" + v.location + "</option>"));
					});
					$("#lean_background").hide();
			    };
			  });
		});
		
	});

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//providers handling
	// on settings form
	$('input[name="addPro"]').on('click',function(e){
		e.preventDefault();
		var newPro = $('input[name="addProvider"]').val();
		$('input[name="addProvider"]').val("");

		// add the location
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { newProvider: newPro }
		})
		  .done(function( msg ) {
		    if (msg) {
		    	//get all the locations and update the list
		    	$.ajax({
				  type: "POST",
				  url: "./api.php",
				  data: { getProviders: 1 }
				})
					.done(function(providers){
						// remove all the locations, and draw them again.
						$("select[name='providers'] > option").remove();
						providers = $.parseJSON(providers);
						$.each(providers, function(k, v){
						    $("select[name='providers']").append($("<option value= '" + v.id + "'>" + v.provider + "</option>"));
						});
					});
		    };
		  });
	});

	$('input[name="deletePro"]').on('click',function(e){
		e.preventDefault();
		var pro = $("select[name='providers']").find(":selected").val();

		// delete the location
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { deleteProvider: pro }
		})
			.done(function(providers){
				// remove all the providers, and draw them again.
				$("select[name='providers'] > option").remove();
				providers = $.parseJSON(providers);
				$.each(providers, function(k, v){
				    $("select[name='providers']").append($("<option value= '" + v.id + "'>" + v.provider + "</option>"));
				});
			});
		   
	});

//template
	$("#saveTemplate").one("click",function(e){
		e.preventDefault();

		var courseName           = $("#id_name").val();
		var courseLocalName      = $("#id_localname").val();
		var courseLocalNameLang  = $("#id_localname_lang").find(":selected").val();
		var coursePurpose        = tinyMCE.get('id_purpose').getContent();
		var courseTarget         = $("#id_target").val();
		var courseTargetDesc     = tinyMCE.get('id_target_description').getContent();
		var courseContent        = tinyMCE.get('id_content').getContent();
		var courseInstructors    = $("#id_instructors").val();
		var courseComment        = tinyMCE.get('id_comment').getContent();
		var courseDurationNumber = $("#id_duration_number").val();
		var courseDurationUnit   = $("#id_duration_timeunit").find(":selected").val();
		var courseCancellation   = tinyMCE.get('id_cancellation').getContent();
		var courseLodging   	 = tinyMCE.get('id_lodging').getContent();
		var courseContact   	 = tinyMCE.get('id_contact').getContent();
		var courseCoordinator    = $("#id_coordinator").find(":selected").val();
		var courseProvider       = $("#id_provider").find(":selected").val();

		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { 
		  	saveTemplate : 1,
		  	courseName   : courseName,
		  	courseLocalName : courseLocalName,
		  	courseLocalNameLang : courseLocalNameLang,
		  	coursePurpose : coursePurpose,
		  	courseTarget : JSON.stringify(courseTarget),
		  	courseTargetDesc : courseTargetDesc,
		  	courseContent : courseContent,
		  	courseInstructors : courseInstructors,
		  	courseComment : courseComment,
		  	courseDurationNumber : courseDurationNumber,
		  	courseDurationUnit : courseDurationUnit,
		  	courseCancellation : courseCancellation,
		  	courseLodging : courseLodging,
		  	courseContact : courseContact,
		  	courseCoordinator : courseCoordinator,
		  	courseProvider : courseProvider

	  	  },
	  	  success : function(e){
				// add the template at the top in the template select
				// $("select[name='providers'] > option").remove();
				// providers = $.parseJSON(providers);
				// $.each(providers, function(k, v){
				//     $("select[name='providers']").append($("<option value= '" + v.id + "'>" + v.provider + "</option>"));
				// });
				alert(e);
			},
			error : function(err){
				alert(err);
			}
		});
			
	});
	
	$("#id_template").change(function(){
		var select = $(this).find(":selected").val();
		
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { 
		  	getTemplate : select,
	  	  },
	  	  success : function(e){
				if ($.isEmptyObject(e)) {
					$('#id_name').val("");
					$('#id_localname').val("");
					$('#id_localname_lang').val("");
					tinyMCE.get('id_purpose').setContent("");
					tinyMCE.get('id_target_description').setContent("");
					$('#id_target').val("");
					tinyMCE.get('id_content').setContent("");
					$('#id_instructors').val("");
					$('#id_duration_number').val("");
					$('#id_duration_timeunit').val("");
					tinyMCE.get('id_cancellation').setContent("");
					tinyMCE.get('id_comment').setContent("");
					tinyMCE.get('id_lodging').setContent("");
					tinyMCE.get('id_contact').setContent("");
					$('#id_coordinator').val("");
					$('#id_provider').val("");
				}else {
					var metacourse = JSON.parse(e);
					$('#id_name').val(metacourse.name);
					$('#id_localname').val(metacourse.localname);
					$('#id_localname_lang').val(metacourse.localname_lang);
					tinyMCE.get('id_purpose').setContent(metacourse.purpose);
					$('#id_target').val(metacourse.target);
					tinyMCE.get('id_content').setContent(metacourse.content);
					tinyMCE.get('id_target_description').setContent(metacourse.target_description);
					$('#id_instructors').val(metacourse.instructors);
					$('#id_comment').val(metacourse.comment);
					$('#id_duration_number').val(metacourse.duration);
					$('#id_duration_timeunit').val(metacourse.duration_unit);
					tinyMCE.get('id_cancellation').setContent(metacourse.cancellation);
					tinyMCE.get('id_comment').setContent(metacourse.comment);
					tinyMCE.get('id_lodging').setContent(metacourse.lodging);
					tinyMCE.get('id_contact').setContent(metacourse.contact);
					$('#id_coordinator').val(metacourse.coordinator);
					$('#id_provider').val(metacourse.provider);
					$('#id_purpose').trigger('change');
					$('#id_content').trigger('change');
					tinyMCE.triggerSave();
				}
			}
		});
	});

// remove the seconds from the duration dropdown
	$('#id_duration_timeunit option[value="1"]').remove();

})();