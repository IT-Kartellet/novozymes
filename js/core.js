(function(){
	//Used as a counter of deleted courses
	var countDeleted = 0;
	var enrolledCourse;
	var calendarInput = $('input[name*=calendar]');
	var itk_course_template = $("div.template").last().clone();
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
		$("[id^='fitem_id_custom_email']").show();
	} else {
		$("[id^='fitem_id_custom_email']").hide();
	}

	$(document.body).on("click","form[action='add_datecourse.php'] input[id='id_submitbutton']",function(){
		// var list = $('input.checkboxgroup1');
		var target_checked = false;
		$('input.checkboxgroup1').each(function() {
			if($(this).prop('checked')) {
				target_checked = true;
			}
		});

		if (!target_checked) {
			alert("Please select at least one target.");
			return false;
		}
	
	});	

	
	$(document.body).on("click","input[id^='id_datecourse_no_dates_']",function(){
		var parent = $(this).closest(".template");
		if ($(this).is(":checked")) {
			parent.find("div[id^='fitem_id_timestart_'], div[id^='fitem_id_timeend_'], div[id^='fitem_id_publishdate_'], div[id^='fitem_id_unpublishdate_'],div[id^='fitem_id_startenrolment_']").remove();
		} else {
			itk_course_template.find("select").val("0");
			itk_course_template.find("input").not("#removeDateCourse").val("");
			parent.html(itk_course_template.html());
		}
	});

	$(document.body).on("click","#addDateCourse",function(){
		//used to duplicate the datecourses;
		var course = $("div.template").last().clone(true, true);
		$("#wrapper").append(course);
		var victim = $(".template").last();
		var index = $('.template').length - 1 + countDeleted;

		victim.find('input').not("#removeDateCourse, [type='checkbox']").val("");

		// Get all input elements"
		var elements = victim.find("select, input:not(#removeDateCourse)");

		$.each(elements, function(ix, element) {
			$label = $('label[for="' + element.id + '"]', victim); // make sure the correct element is focused when clicking the label
			$label.attr('for', $label.attr('for').replace(/\d+/, index));

			element.name = element.name.replace(/\d+/, index); // Update the element itself
			element.id = element.id.replace(/\d+/, index);

			$wrapper = $(element).closest('.fitem'); // And the id of the wrapper
			$wrapper[0].id = $wrapper[0].id.replace(/\d+/, index);
		});

		victim.find('#removeDateCourse').attr('class', 'none');

		// update dates
		var today = new Date();

		var dd = today.getDate();
		var m = 0;
		var h = 0;
		var mm = today.getMonth()+1;
		var yyyy = today.getFullYear();

		victim.find("select[name*='minutes']").val(m);
		victim.find("select[name*='hour']").val(h);
		victim.find("select[name*='day']").val(dd);
		victim.find("select[name*='month']").val(mm);
		victim.find("select[name*='year']").val(yyyy);

		M.block_metacourse.dateform.init();
	});

	// don't screw this up
	$(document.body).on("click","#removeDateCourse",function(){
		if ($("div.template").length < 2) {
			alert("You cannot remove this. Select the 'No dates' checkbox if you don't need any dates.");
			return false;
		}

		if (confirm("Please note – unless the course has already been held, that this will delete the date from all files – including the participants ”My courses” file.")) {
			var klass = $(this).attr('class');

			$(this).parent(".template").remove();

			if (klass !== 'none') {
				$("input[name='datecourse["+ klass +"][deleted]']").val(1);
			}
			countDeleted++;
		}
	});


	// modal window for the TOS dialog
	$(document.body).on('click','div.addToWaitingList input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background").show();
		$("#waitingSpan").show();
		if (!$('#lean_background input[name="accept"]').is(":checked")) {
			$('#lean_background input[name="submit"]').prop('disabled',true);
		}
	});

	//enrol me
	$(document.body).on('click','div.enrolMeButton:not(.elearning) input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background").show();
		if (!$('#lean_background input[name="accept"]').is(":checked")) {
			$('#lean_background input[name="submit"]').prop('disabled',true);
		}
	});

	$(document.body).on('click','#accept_enrol', function(e){
		var $this = $(this);
		$this.prop('disabled', true);
		$this.siblings('input[name="cancel"]').prop('disabled', true);

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
	$(document.body).on('click','div.unEnrolMeButton:not(.elearning) input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background_unenrol").show();
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

	$(document.body).on('click','#lean_background_unenrol input[name="cancel"]', function(){
		$('#lean_background_unenrol').hide();
		$('#lean_background_unenrol input[name="accept_unenrol"]').prop('checked',false);
		$('#lean_background_unenrol input[name="submit"]').prop('disabled',true);
	});


	// Add me to waiting list
	$(document.body).on('click','div.addToWaitingList input',function(e){
		e.preventDefault();

		enrolledCourse = $(this);
		window.scrollTo(0, 0);

		$("#lean_background_waiting").show();
		if (!$('#lean_background_waiting input[name="accept"]').is(":checked")) {
			$('#lean_background_waiting input[name="submit"]').prop('disabled',true);
		}
	});

	$(document.body).on('click','#lean_background_waiting input[name="accept"]', function(){
		if ($('#lean_background_waiting input[name="submit"]').is(":disabled")) {
			$('#lean_background_waiting input[name="submit"]').prop('disabled',false);
		} else {
			$('#lean_background_waiting input[name="submit"]').prop('disabled',true);
		}
		
	});

	$(document.body).on('click','#accept_unenrol', function(e){
		var $this = $(this);
		$this.prop('disabled', true);
		$this.siblings('input[name="cancel"]').prop('disabled', true);

		enrolledCourse.closest('form').submit();
	});


	$(document.body).on('click','#lean_background_waiting input[name="cancel"]', function(){
		$('#lean_background_waiting').hide();
		$('#lean_background_waiting input[name="accept_unenrol"]').prop('checked',false);
		$('#lean_background_waiting input[name="submit"]').prop('disabled',true);
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
		  return false;
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
		  	
		  },
		  error: function(e){
		  	
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
		  	
		  },
		  error: function(e){
		  	
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
		  	alert("Success!");
		  },
		  error: function(e){
		  	alert("Could not enrol him.");
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

	$('input[name="renameLoc"]').on('click',function(e){
		e.preventDefault();
		var locId = $("select[name='locations']").find(":selected").val();
		var locText = $("#id_renameLocation").val();
		$("#id_renameLocation").val("");

		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { renameLocationID: locId, renameLocationText: locText }
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


	$('input[name="renamePro"]').on('click',function(e){
		e.preventDefault();
		var proId = $("select[name='providers']").find(":selected").val();
		var proText = $("#id_renameProvider").val();
		$("#id_renameProvider").val("");
		$.ajax({
		  type: "POST", 
		  url: "./api.php",
		  data: { renameProviderID: proId, renameProviderText: proText }
		})
			.done(function(providers){
				$("select[name='providers'] > option").remove();
				providers = $.parseJSON(providers);
				$.each(providers, function(k, v){
				    $("select[name='providers']").append($("<option value= '" + v.id + "'>" + v.provider + "</option>"));
				});
			});
	});

	// on the datecourse form
	$(document.body).on('click','.anotherLocation' ,function(e){
		e.preventDefault();
		window.scrollTo(0, 0);
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
		console.log("CLICK");
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
		var coursePrice          = $("#id_price").val();
		var courseCurrencyId     = $("#id_currencyid").find(":selected").val();
		var courseCancellation   = tinyMCE.get('id_cancellation').getContent();
		var courseLodging   	 = tinyMCE.get('id_lodging').getContent();
		var courseContact   	 = tinyMCE.get('id_contact').getContent();
		var courseCoordinator    = $("#id_coordinator").find(":selected").val();
		var courseProvider       = $("#id_provider").find(":selected").val();
		var courseNoDatesEnabled = $("#id_nodates_enabled").is(':checked') ? 1 : 0;

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
			coursePrice : coursePrice,
			courseCurrencyId : courseCurrencyId,
		  	courseCancellation : courseCancellation,
		  	courseLodging : courseLodging,
		  	courseContact : courseContact,
		  	courseCoordinator : courseCoordinator,
		  	courseProvider : courseProvider,
			courseNoDatesEnabled : courseNoDatesEnabled
	  	  },
	  	  success : function(e){
				// add the template at the top in the template select
				// $("select[name='providers'] > option").remove();
				// providers = $.parseJSON(providers);
				// $.each(providers, function(k, v){
				//     $("select[name='providers']").append($("<option value= '" + v.id + "'>" + v.provider + "</option>"));
				// });
				//console.log(e);
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
					$('#id_price').val("");
					$('#id_currencyid').val(0);
					tinyMCE.get('id_cancellation').setContent("");
					tinyMCE.get('id_comment').setContent("");
					tinyMCE.get('id_lodging').setContent("");
					tinyMCE.get('id_contact').setContent("");
					$('#id_coordinator').val("");
					$('#id_provider').val("");
					$('#id_nodates_enabled').prop("checked", false);
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
					$('#id_price').val(metacourse.price);
					$('#id_currencyid').val(metacourse.currencyid===null ? 0 : metacourse.currencyid);
					tinyMCE.get('id_cancellation').setContent(metacourse.cancellation);
					tinyMCE.get('id_comment').setContent(metacourse.comment);
					tinyMCE.get('id_lodging').setContent(metacourse.lodging);
					tinyMCE.get('id_contact').setContent(metacourse.contact);
					$('#id_coordinator').val(metacourse.coordinator);
					$('#id_provider').val(metacourse.provider);
					$('#id_purpose').trigger('change');
					$('#id_content').trigger('change');
					$('#id_nodates_enabled').prop("checked", metacourse.nodates_enabled==0 ? false : true);
					tinyMCE.triggerSave();
				}
			}
		});
	});

// remove the seconds from the duration dropdown
	$('#id_duration_timeunit option[value="1"]').remove();

})();