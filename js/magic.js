(function(){

	//the metacourse object
	var course = {};

//html appending
	var victim = $('<div id="victim"></div>');
	victim.appendTo($('body'));
	victim = document.getElementById('victim');

	var welcome = '<div class="block_metacourse over" id="welcome"> \
			<h1>Welcome!</h1>\
			<div>\
				<p>What would you like to do?</p>\
			</div>\
			<div>\
				<button id="viewBtn">View/Edit Courses</button>\
				<button id="addBtn">Add a course</button>\
				<button class="cancel">Cancel</button>\
			</div>\
		</div>\
'; 

	var listCourses = '<div class="block_metacourse over" id="listCourses">\
			<h1>List of current courses</h1>\
			<ol>\
				<li>First course</li>\
				<li>Second course</li>\
				<li>Third course</li>\
			</ol>\
			<button class="cancel">Cancel</button>\
		</div>';

	var addName = '<div class="block_metacourse over" id="addName">\
			<h1>What is the name of the course?</h1>\
			<input id="courseName" type="text" name="courseName" value="" />\
			<button id="addNameBack">Back</button>\
			<button id="addNameNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>'
	var addPurpose = '<div class="block_metacourse over" id="addPurpose">\
			<h1>What is the purpose of the course?</h1>\
			<input id="purpose" type="text" name="purpose" value="" />\
			<button id="addPurposeBack">Back</button>\
			<button id="addPurposeNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';

	var addTarget = '<div class="block_metacourse over" id="addTarget">\
			<h1>What is the target group of the course?</h1>\
			<input id="target" type="text" name="target" value="" />\
			<button id="addTargetBack">Back</button>\
			<button id="addTargetNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';

	var addContent = '<div class="block_metacourse over" id="addContent">\
			<h1>What is the content of the course?</h1>\
			<input id="content" type="text" name="content" value="" />\
			<button id="addContentBack">Back</button>\
			<button id="addContentNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';
	var addInstructors = '<div class="block_metacourse over" id="addInstructors">\
			<h1>Who is the instructor of this course?</h1>\
			<input id="instructor" type="text" name="instructor" value="" />\
			<button id="addInstuctorBack">Back</button>\
			<button id="addInstructorNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';
	var addComment = '<div class="block_metacourse over" id="addComment">\
			<h1>Add a comment for this course</h1>\
			<input id="comment" type="text" name="comment" value="" />\
			<button id="addCommentBack">Back</button>\
			<button id="addCommentNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';
	var addCoordinator = '<div class="block_metacourse over" id="addCoordinator">\
			<h1>Who is the coordinator of this course?</h1>\
			<input id="coordinator" type="text" name="cooridinator" value="" />\
			<button id="addCoordinatorBack">Back</button>\
			<button id="addCoordinatorNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';
	var addProvider = '<div class="block_metacourse over" id="addProvider">\
			<h1>Who provides this course?</h1>\
			<input id="provider" type="text" name="provider" value="" />\
			<button id="addProviderBack">Back</button>\
			<button id="addProviderNext">Next</button>\
			<button class="cancel">Cancel</button>\
		</div>';
	var addDateCourses = '<div class="block_metacourse over" id="addDateCourses">\
			<h1>Insert below the options for the courses</h1>\
			<div id="dataTable"></div>\
			<button id="addDateCourseBack">Back</button>\
			<button id="finish">Finish</button>\
			<button class="cancel">Cancel</button>\
		</div>';

	victim.innerHTML += welcome;	
	victim.innerHTML += listCourses;
	victim.innerHTML += addName;
	victim.innerHTML += addPurpose;
	victim.innerHTML += addTarget;
	victim.innerHTML += addContent;
	victim.innerHTML += addInstructors;
	victim.innerHTML += addComment;
	victim.innerHTML += addCoordinator;
	victim.innerHTML += addProvider;
	victim.innerHTML += addDateCourses;
	


//EVENTS WELCOME PAGE
	$(document).on('click','#magic', function(){
		$("div#welcome").slideToggle();
	});
	$(document).on('click','#viewBtn', function(){
		$("div#listCourses").slideToggle();
	});
	$(document).on('click','.cancel', function(){
		$("div.over").slideUp();
	});
	$(document).on('click','#addBtn', function(){
		$("div#addName").slideToggle();
	});

//EVENTS NAME PAGE
	$(document).on('click','#addNameBack', function(){
		$("div.over").slideUp();
		$('div#welcome').slideDown();
	});
	$(document).on('click','#addNameNext', function(){
		$("div.over").slideUp();
		course.name = $('input#courseName').val();
		$('div#addPurpose').slideDown();
		// console.log(course);
	});

//EVENTS PURPOSE PAGE
	$(document).on('click','#addPurposeNext', function(){
		$("div.over").slideUp();
		course.purpose = $('input#purpose').val();
		$('div#addTarget').slideDown();
		// console.log(course);
	});

//EVENTS TARGET PAGE
	$(document).on('click','#addTargetNext', function(){
		$("div.over").slideUp();
		course.target = $('input#target').val();
		$('div#addContent').slideDown();
		// console.log(course);
	});

///EVENTS CONTENT PAGE
	$(document).on('click','#addContentNext', function(){
		$("div.over").slideUp();
		course.content = $('input#content').val();
		$('div#addInstructors').slideDown();
		// console.log(course);
	});

//EVENTS INSTRUCTOR PAGE
	$(document).on('click','#addInstructorNext', function(){
		$("div.over").slideUp();
		course.instructor = $('input#instructor').val();
		$('div#addComment').slideDown();
		// console.log(course);
	});

//EVENTS COMMENT PAGE
	$(document).on('click','#addCommentNext', function(){
		$("div.over").slideUp();
		course.comment = $('input#comment').val();
		$('div#addCoordinator').slideDown();
		// console.log(course);
	});

//EVENTS COORDINATOR PAGE
	$(document).on('click','#addCoordinatorNext', function(){
		$("div.over").slideUp();
		course.coordinator = $('input#coordinator').val();
		$('div#addProvider').slideDown();
		// console.log(course);
	});

//EVENTS PROVIDER PAGE
	$(document).on('click','#addProviderNext', function(){
		$("div.over").slideUp();
		course.provider = $('input#provider').val();
		$('div#addDateCourses').slideDown();
		// console.log(course);
	});
// EVENTS
	$(document).on('click','#finish', function(){
		$("div.over").slideUp();
		// course.provider = $('input#provider').val();
		// $('div#addDateCourses').slideDown();
		course.dateCourses = $('#dataTable').handsontable('getData');
		$.post("blocks/metacourse/api.php",{'addCourse':JSON.stringify(course)})
			.done(function(data) {
    			console.log(data);
			});
	});

//populate the datecourse table

  var data = [["","","","","",""]];
  $("#dataTable").handsontable({
    data: data,
    startRows: 6,
    startCols: 6,
    minSpareRows: 1,
    colHeaders: ['Start date','End date', 'Location','Language','Price','Total places'],
    columns: [
	    {
	      // type: 'date',
	      // dateFormat: 'yyyy-mm-dd'
	    },
	    {
	      // type: 'date',
	      // dateFormat: 'yyyy-mm-dd'
	    },
	    {
	    	//location
	    },
	    {
	    	//language
	    },
	    {
	    	type: 'numeric'
	    },
	    {
	    	type: 'numeric'
	    }
	]
  });
})();