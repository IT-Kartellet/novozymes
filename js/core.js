(function(){
	$('#addDateCourse').unbind('submit');
	$(document.body).on("click","#addDateCourse",function(){
		var course = $("div.template").last().clone();
		$("#wrapper").append(course);
		var victim = $(".template").last();
		var index = $('.template').length;

		victim.find('input').val("");
		victim.find('select').val("0");
		victim.find("select[name='timestart[" + (index-2) + "][day]']").attr("name", "timestart[" + (index-1) + "][day]");
		victim.find("select[name='timestart[" + (index-2) + "][month]']").attr("name", "timestart[" + (index-1) + "][month]");
		victim.find("select[name='timestart[" + (index-2) + "][year]']").attr("name", "timestart[" + (index-1) + "][year]");

		victim.find("select[name='timeend[" + (index-2) + "][day]']").attr("name", "timeend[" + (index-1) + "][day]");
		victim.find("select[name='timeend[" + (index-2) + "][month]']").attr("name", "timeend[" + (index-1) + "][month]");
		victim.find("select[name='timeend[" + (index-2) + "][year]']").attr("name", "timeend[" + (index-1) + "][year]");

		victim.find("input[name='datecourse[" + (index-2) + "][location]']").attr("name", "datecourse[" + (index-1) + "][location]");
		victim.find("select[name='datecourse[" + (index-2) + "][language]']").attr("name", "datecourse[" + (index-1) + "][language]");


		victim.find("input[name='datecourse[" + (index-2) + "][price]']").attr("name", "datecourse[" + (index-1) + "][price]");
		victim.find("input[name='datecourse[" + (index-2) + "][places]']").attr("name", "datecourse[" + (index-1) + "][places]");
	});
})();