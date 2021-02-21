<?php
/*
	functions-jcumap.php

	by © 2020–2021 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/





// This function reads JSON files and returns the data as an associative array
function getDefinition($code)
{
	$jsonFile = './definitions/' . $code . '.json';
	$json = false;
	if ( file_exists($jsonFile) )
	{
		$rawJsonData = file_get_contents($jsonFile);
		$json = json_decode($rawJsonData);
	}
	return $json;
}





// This function returns an array of the assessment types that are coded into JCUMap
function listAssessmentTypes()
{
	$types = array();
	
	$type = new stdClass();
	$type->category = 'Exam';
	$type->type = 'Test/Exam (Invigilated)';
	$types[0] = $type;
	
	$type = new stdClass();
	$type->category = 'Exam';
	$type->type = 'Test/Quiz (Non-Invigilated)';
	$types[1] = $type;
	
	$type = new stdClass();
	$type->category = 'Exam';
	$type->type = 'Skill Test (Demonstration/Laboratory/Studio/Clinic/Field/Other)';
	$types[2] = $type;
	
	$type = new stdClass();
	$type->category = 'Exam';
	$type->type = 'Objective Structured Clinical Examination';
	$types[3] = $type;
	
	$type = new stdClass();
	$type->category = 'Oral &amp; Performance';
	$type->type = 'Creative Work';
	$types[4] = $type;
	
	$type = new stdClass();
	$type->category = 'Oral &amp; Performance';
	$type->type = 'Participation/Leadership';
	$types[5] = $type;
	
	$type = new stdClass();
	$type->category = 'Oral &amp; Performance';
	$type->type = 'Performance (Artistic/Exhibition/Moot Court/Other)';
	$types[6] = $type;
	
	$type = new stdClass();
	$type->category = 'Oral &amp; Performance';
	$type->type = 'Presentation (Seminar/Debate/Forum/Critique/Other)';
	$types[7] = $type;
	
	$type = new stdClass();
	$type->category = 'Oral &amp; Performance';
	$type->type = 'Teamwork Performance Evaluation';
	$types[8] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Dissertation/Thesis/Research Paper';
	$types[9] = $type;
	
	$type->category = 'Written Discourse';
	$type->type = 'Journal (Field/WIL/Laboratory/Reflective/Other)';
	$types[10] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Portfolio';
	$types[11] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Poster';
	$types[12] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Proposal';
	$types[13] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Report (Experimental/Analytical)';
	$types[14] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Report (Project/Design/Research)';
	$types[15] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Review (Literature/Critical)';
	$types[16] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Tutorial Submission/Workbook/Logbook';
	$types[17] = $type;
	
	$type = new stdClass();
	$type->category = 'Written Discourse';
	$type->type = 'Other Writing (Abstract/Annotated Bibliography/Case Study/Essay/Other)';
	$types[18] = $type;
	
	$type = new stdClass();
	$type->category = 'Vocational';
	$type->type = 'Professional Practice (Planning/Execution/Report)';
	$types[19] = $type;
	
	$type = new stdClass();
	$type->category = 'Vocational';
	$type->type = 'Software/Manufactured Design/Other Physical Output';
	$types[20] = $type;
	
	return $types;
}
