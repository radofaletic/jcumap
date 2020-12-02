<!DOCTYPE html>
<html lang="en">
<?php
/*
	index.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);





require_once('./functions-jcumap.php');
require_once('./functions-html.php');
require_once('./functions-course.php');
require_once('./functions-major.php');
require_once('./functions-program.php');





$accreditationDisplayScript = '';
if ( isset($_GET['accreditationDisplay']) || ( isset($displayInformationForAccreditation) && $displayInformationForAccreditation) )
{
	$accreditationDisplayScript = 'accreditation.php';
}
$urlDisplayType = 'pretty';//'php'
$urlPrefix = '/';
$urlScript = ( $accreditationDisplayScript ) ? $accreditationDisplayScript : 'index.php';


	







/*
	Display information for the given program/major/course code
*/
$code = false;
$codeCourse = false;
$codeMajor = false;
$codeProgram = false;
$codeFDD = false;
if ( isset($_GET['program']) && strlen($_GET['program']) )
{
	$tmpCode = strtoupper(trim($_GET['program']));
	if ( 5 <= strlen($tmpCode) && strlen($tmpCode) <= 6 && ctype_upper($tmpCode) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeProgram = $tmpCode;
}
$programDefinitions = getDefinition('programs');
if ( isset($_GET['major']) && strlen($_GET['major']) )
{
	$tmpCode = strtoupper(trim($_GET['major']));
	if ( strlen($tmpCode) == 8 && ctype_upper( substr($tmpCode, 0, 4) ) && ctype_upper( substr($tmpCode, -3) ) && substr($tmpCode, 4, 1) == '-' )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeMajor = $tmpCode;
}
if ( isset($_GET['course']) && strlen($_GET['course']) )
{
	$tmpCode = strtoupper(trim($_GET['course']));
	if ( strlen($tmpCode) == 8 && ctype_upper( substr($tmpCode, 0, 4) ) && ctype_digit( substr($tmpCode, -4) ) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeCourse = $tmpCode;
}
if ( $accreditationDisplayScript && isset($_GET['fdd']) && strlen($_GET['fdd']) )
{
	$tmpCode = strtoupper(trim($_GET['fdd']));
	if ( 3 <= strlen($tmpCode) && ctype_upper($tmpCode) && !in_array($tmpCode, $programDefinitions) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeFDD = $tmpCode;
}
if ( isset($_GET['code']) && strlen($_GET['code']) )
{
	$code = strtoupper(trim($_GET['code']));
	$name = '';
	$shortname = '';
	$type = false;
	$program = false;
	$major = false;
	$course = false;
	$fdd = false;
	$courseCodes = array();
	if ( $codeCourse && strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$type = 'course';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeMajor && strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == '-' )
	{
		$type = 'major';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeProgram && 5 <= strlen($code) && strlen($code) <= 6 && ctype_upper($code) )
	{
		$type = 'program';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeFDD && 3 <= strlen($code) && ctype_upper($code) )
	{
		$type = 'fdd';
	}
	else
	{
		$code = false;
	}
	switch ($type)
	{
		case 'course':
			createCourseDetails($code, $courseCodes, $course, $name, $shortname);
			break;
		case 'major':
			createMajorDetails($code, $codeProgram, $major, $program, $name, $shortname);
			break;
		case 'program':
			createProgramDetails($code, $program, $name, $shortname);
			break;
		case 'fdd':
			createFDDDetails($code, $fdd, $name, $shortname);
			break;
	}
	if ( !$code || !$type )
	{
		$code = false;
	}
	
	echo '<head>' . PHP_EOL;
	echo '	<meta charset="utf-8">' . PHP_EOL;
	echo '	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' . PHP_EOL;
	echo '	<meta name="description" content="CECS Professional Skills Mapping">' . PHP_EOL;
	echo '	<meta name="author" content="Rado Faletič">' . PHP_EOL;
	echo '	<meta name="keywords" content="CECS,EA,Engineers Australia,engineering,mapping">' . PHP_EOL;
	echo '	<meta name="format-detection" content="telephone=no">' . PHP_EOL;
	
	echo customCSS('	', PHP_EOL);
		
	echo '	<title>' . $shortname . ' :: Professional Skills Mapping :: CECS :: ANU</title>' . PHP_EOL;
	echo '</head>' . PHP_EOL;
	
	echo '<body class="container">' . PHP_EOL;
	echo '	<header><h1 class="display-1 text-center"><a class="text-reset" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '">CECS Professional Skills Mapping</a></h1></header>' . PHP_EOL;
	if ( $code && $type && $name )
	{
		switch ($type)
		{









/*
	Display mapping information about the course
*/
			case 'course':
				print("<section>\n");
				print("<h2>" . $name . "</h2>\n");
				print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: information provided here is indicative only. For full and current course information view the official page on P&amp;C.</div>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>code: </th><td>" . $course->code . "</td></tr>\n");
				print("		<tr><th>name: </th><td class=\"fst-italic\">" . $course->name . "</td></tr>\n");
				if ( $accreditationDisplayScript )
				{
					print("		<tr><th><i>JCUMap</i> files: </th><td><ul class=\"m-0\">");
					if ( file_exists("courses/" . $course->fileName . ".xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "courses/" . $course->fileName . ".xml\" download>" . $course->fileName . ".xml</a></li>");
					}
					if ( file_exists("courses/" . $course->fileName . "MappingResult.xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "courses/" . $course->fileName . "MappingResult.xml\" download>" . $course->fileName . "MappingResult.xml</a></li>");
					}
					print("</ul></td></tr>\n");
				}				
				if ( isset($course->units) )
				{
					print("		<tr><th>unit value: </th><td>" . number_format($course->units, 0) . "</td></tr>\n");
				}
				if ( isset($course->description) && $course->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . str_replace("\n", "<br> ", str_replace("\r\n", "<br> ", trim($course->description))) . "</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($course->code) . "\">" . generateLinkToProgramsAndCourses($course->code) . "</a></td></tr>\n");
				if ( isset($course->learningOutcomes) && $course->learningOutcomes )
				{
					print("		<tr><th>learning outcomes: </th><td class=\"small\"><ol>");
					foreach ($course->learningOutcomes as $learningOutcome)
					{
						if ( $learningOutcome )
						{
							print("<li>" . $learningOutcome . "</li>");
						}
					}
					print("</ol></td></tr>\n");
				}
				if ( isset($course->assessments) && $course->assessments )
				{
					print("		<tr><th>assessment: </th><td class=\"small\">");
					if ( $accreditationDisplayScript )
					{
						$assessmentTypes = listAssessmentTypes();
						print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"fst-italic\">assessment breakdown</caption>");
						print("<colgroup><col span=\"1\"><col span=\"1\"><col span=\"1\"><col span=\"" . count($course->learningOutcomes) . "\"></colgroup>");
						print("<thead class=\"bg-light\"><tr><th rowspan=\"2\">assessment item(s)</th><th rowspan=\"2\">category</th><th rowspan=\"2\" class=\"text-center\">weight</th><th colspan=\"" . count($course->learningOutcomes) . "\" class=\"text-center\">percentage breakdown (per 100% item)</th></tr><tr>");
						foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
						{
							print("<th class=\"text-center\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" title=\"" . ( $learningOutcomeN + 1 ). ". " . $learningOutcome . "\">" . ( $learningOutcomeN + 1 ). "</th>");
						}
						print("</tr></thead><tbody>");
						foreach ($course->assessments as $assessmentN => $assessment)
						{							
							print("<tr><td><ol class=\"m-0\" start=\"" . ( $assessmentN + 1 ) . "\"><li>" . $assessment->name . "</li></ol></td><td>" . $assessmentTypes[$assessment->typeCode]->type . "</td><td class=\"text-center fw-bold\">");
							if ( $assessment->weight > 0 )
							{
								print($assessment->weight . "%");
							}
							print("</td>");
							foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
							{
								print("<td class=\"text-center\">");
								if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) && $course->assessmentsMapping[$assessmentN][$learningOutcomeN] > 0)
								{
									print($course->assessmentsMapping[$assessmentN][$learningOutcomeN] . "%");
								}
								print("</td>");
							}
							print("</tr>");
						}
						print("</tbody></table>");
						if ( $course->assessmentCategorisationSummary )
						{
							$assessmentTotals = array_sum($course->assessmentCategorisationSummary);
							print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"fst-italic\">assessment types used across whole subject</caption>");
							print("<thead class=\"bg-light\"><tr><th>assessment type</th><th colspan=\"2\" class=\"text-center\">contribution to overall assessment</th></tr></thead><tbody>");
							foreach ($course->assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
							{
								if ( $assessmentTypeCredits > 0.0 )
								{
									$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
									if ( $assessmentTypePercentage == 0 )
									{
										$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 1);
									}
									print("<tr><td class=\"align-middle\">" . $assessmentTypes[$assessmentType]->type . "</td><td class=\"text-center align-middle\">" . $assessmentTypePercentage . "%</td><td class=\"col-6 align-middle\"><div class=\"progress bg-transparent\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width: " . $assessmentTypePercentage . "%\" aria-valuenow=\"" . $assessmentTypePercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $assessmentTypePercentage . "%\"></div></div></td></tr>");
								}
							}
							print("</tbody></table>");
						}
					}
					else
					{
						print("<ol>");
						foreach ($course->assessments as $assessment)
						{
							print("<li>" . $assessment->name);
							if ( $assessment->weight > 0 )
							{
								print(" (" . $assessment->weight . "%)");
							}
							print("</li>");
						}
						print("</ol>");
					}
					print("</td></tr>\n");
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// table of all learning outcomes mappings (to EA competencies and to assessment items)
				print("<section>\n");
				print("<h2>Mapped learning outcomes</h2>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-bordered table-hover small\">\n");
				print("	<colgroup>\n");
				print("		<col span=\"1\">\n");
				foreach ($course->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("		<col span=\"" . $competency->sublevels . "\">\n");
					}
				}
				if ( $course->assessments )
				{
					print("		<col span=\"" . count($course->assessments) . "\">\n");
				}
				print("	</colgroup>\n");
				print("	<thead class=\"bg-light\">\n");
				print("		<tr><th rowspan=\"2\" class=\"text-center align-middle\">learning outcome</th>");
				foreach ($course->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("<th colspan=\"" . $competency->sublevels . "\" class=\"text-center\">" . $competency->label . " " . $competency->text . "</th>");
					}
				}
				if ( $course->assessments )
				{
					print("<th colspan=\"" . count($course->assessments) . "\" class=\"text-center\">assessment tasks</th>");
				}
				print("</tr>\n");
				print("		<tr>");
				foreach ($course->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 2 )
					{
						print("<th class=\"text-center align-middle\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</th>");
					}
				}
				if ( $course->assessments )
				{
					foreach ($course->assessments as $assessmentN => $assessment)
					{
						print("<th class=\"text-center align-middle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" . $assessment->name);
						if ( $accreditationDisplayScript )
						{
							print(" (" . $assessment->weight . "%)");
						}
						print("\">" . ( $assessmentN + 1 ) . "</th>");
					}
				}
				print("</tr>\n");
				print("	</thead>\n");
				print("	<tbody>\n");
				foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
				{
					if ( $learningOutcome )
					{
						print("		<tr><td><ol class=\"m-0\" start=\"" . ( $learningOutcomeN + 1 ) . "\"><li>" . $learningOutcome . "</li></ol></td>");
						foreach ($course->competencies as $competencyKey => $competency)
						{
							if ( $competency->level == 2 )
							{
								print("<td class=\"text-center text-success align-middle\">");
								if ( isset($course->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $course->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0)
								{
									if ( $accreditationDisplayScript )
									{
										for ($i=0; $i<$course->learningOutcomesMapping[$learningOutcomeN][$competencyKey]; $i++)
										{
											print("✓");
										}
									}
									else
									{
										print("✓");
									}
								}
								print("</td>");
							}
						}
						if ( $course->assessments )
						{
							foreach ($course->assessments as $assessmentN => $assessment)
							{
								print("<td class=\"text-center text-success align-middle\"");
								if ( $accreditationDisplayScript )
								{
									print(" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" title=\"");
									if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) )
									{
										print($course->assessmentsMapping[$assessmentN][$learningOutcomeN]);
									}
									else
									{
										print("0");
									}
									print("% of assessment #" . ( $assessmentN + 1 ) . "\"");
								}
								print(">");
								if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) && $course->assessmentsMapping[$assessmentN][$learningOutcomeN] > 0)
								{
									print("✓");
								}
								print("</td>");
							}
						}
						print("</tr>\n");
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// display chart of development level learning against each of the competencies
				print("<section>\n");
				print("<h2>Course contribution towards the " . $course->competencyName . "</h2>\n");
				if ( $accreditationDisplayScript )
				{
					print("<p>This table maps how the unit credits of this course contribute towards achievement of the " . $course->competencyName . ".</p>\n");
				}
				else
				{
					print("<p>This table depicts the relative contribution of this course towards the " . $course->competencyName . ". <em>Note that this illustration is indicative only, and may not take into account any recent changes to the course. You are advised to review the official course page on P&amp;C for current information.</em>.</p>\n");
				}
				$maxUnits = 1;
				foreach ($course->mappingData as $competencyLabel => $DLs)
				{
					$maxUnits = max($maxUnits, ceil(array_sum($DLs)));
				}
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-hover small\">\n");
				if ( $accreditationDisplayScript )
				{
					print("	<thead class=\"bg-light sticky-top\"><tr><th class=\"col-1\"></th><th class=\"text-left border-left\">0.0</th>");
					print("<th class=\"text-right border-right\">" . $maxUnits . ".0</th>");
					print("</tr></thead>\n");
				}
				print("	<tbody>\n");
				foreach ($course->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("		<tr class=\"table-secondary\"><td colspan=\"3\" class=\"fw-bold\">" . $competency->label . " " . $competency->text . "</td></tr>\n");
					}
					else if ( $competency->level == 2 )
					{
						print("		<tr class=\"border-bottom\"><td class=\"text-center align-middle border-right col-1\" data-toggle=\"tooltip\" data-placement=\"left\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</td><td colspan=\"2\" class=\"align-middle\">\n");
						print("			<div class=\"progress bg-transparent\">");
						if ( $accreditationDisplayScript )
						{
							if ( isset($course->mappingData[$competency->label][1]) && $course->mappingData[$competency->label][1] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][1] / $maxUnits;
								print("<div class=\"progress-bar bg-success text-left\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $course->mappingData[$competency->label][1] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($course->mappingData[$competency->label][1], 2) . "\">DL1</div>");
							}
							if ( isset($course->mappingData[$competency->label][2]) && $course->mappingData[$competency->label][2] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][2] / $maxUnits;
								print("<div class=\"progress-bar bg-primary text-center\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $course->mappingData[$competency->label][2] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($course->mappingData[$competency->label][2], 2) . "\">DL2</div>");
							}
							if ( isset($course->mappingData[$competency->label][3]) && $course->mappingData[$competency->label][3] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][3] / $maxUnits;
								print("<div class=\"progress-bar bg-danger text-right\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $course->mappingData[$competency->label][3] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($course->mappingData[$competency->label][3], 2) . "\">DL3</div>");
							}
						}
						else
						{
							$sumOfUnits = array_sum($course->mappingData[$competency->label]);
							if ( $sumOfUnits > 0.0 )
							{
								$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
								print("<div class=\"progress-bar\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\"></div>");
							}
						}
						print("</div>\n");
						print("		</td></tr>\n");
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// list all EA competencies, and indicate which are addressed in this course
				print("<section>\n");
				print("<h2>" . $course->competencyName . " — summary</h2>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-hover\">\n");
				print("	<tbody>\n");
				foreach ($course->competencies as $competencyKey => $competency)
				{
					switch ($competency->level)
					{
						case 1:
							print("		<tr class=\"table-secondary\">");
							print("<th colspan=\"5\">" . $competency->label . " " . $competency->text . "</th>");
							print("</tr>\n");
							break;
						case 2:
							print("		<tr class=\"small\">");
							if ( $competency->competencyLevel > 0 )
							{
								print("<td class=\"text-center text-success align-middle\">");
								if ( $accreditationDisplayScript )
								{
									for ($i=0; $i<$competency->competencyLevel; $i++)
									{
										print("✓");
									}
								}
								else
								{
									print("✓");
								}
								print("</td>");
							}
							else
							{
								print("<td></td>");
							}
							print("<td></td><td>" . $competency->label . "</td><td colspan=\"2\">" . $competency->text . "</td>");
							print("</tr>\n");
							break;
						case 3:
							if ($accreditationDisplayScript)
							{
								print("		<tr class=\"small\">");
								if ( $competency->competencyLevel > 0 )
								{
									print("<td class=\"text-center text-success align-middle\">");
									if ( $accreditationDisplayScript )
									{
										for ($i=0; $i<$competency->competencyLevel; $i++)
										{
											print("✓");
										}
									}
									else
									{
										print("✓");
									}
									print("</td>");
								}
								else
								{
									print("<td></td>");
								}
								print("<td colspan=\"2\"></td><td>" . $competency->label . "</td><td>" . $competency->text . "</td>");
								print("</tr>\n");
							}
							break;
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				break;










/*
	Display mapping information about the major
*/
			case 'major':
				print("<section>\n");
				print("<h2>" . $name . "</h2>\n");
				print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: information provided here is indicative only. For full and current information about this major view the official page on P&amp;C.");
				if ( !isset($program) || !$program || !isset($program->name) )
				{
					print("<br>\n This page depicts information about this major in a stand-alone fashion, i.e. not including the core degree it is a part of. To see the combined information of this major as part of a full degree, select one of the “<a class=\"alert-link\" href=\"#parentPrograms\">parent program(s)</a>” below.");
				}
				print("</div>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				if ( $program && isset($program->name) )
				{
					print("		<tr><th>program: </th><td class=\"fst-italic\">" . $shortname . "</td></tr>\n");
				}
				else
				{
					print("		<tr><th>major: </th><td class=\"fst-italic\">" . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . " major</td></tr>\n");
				}
				if ($accreditationDisplayScript)
				{
					print("		<tr><th><i>JCUMap</i> files: </th><td><ul class=\"m-0\">");
					if ( file_exists("majors/" . $major->code . ".xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "majors/" . $major->code . ".xml\" download>" . $major->code . ".xml</a></li>");
					}
					if ( file_exists("majors/" . $major->code . "MappingResult.xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "majors/" . $major->code . "MappingResult.xml\" download>" . $major->code . "MappingResult.xml</a></li>");
					}
					if ( $program && isset($program->code) && file_exists("programs/" . $program->code . ".xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "programs/" . $program->code . ".xml\" download>" . $program->code . ".xml</a></li>");
					}
					if ( $program && isset($program->code) && file_exists("programs/" . $program->code . "MappingResult.xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "programs/" . $program->code . "MappingResult.xml\" download>" . $program->code . "MappingResult.xml</a></li>");
					}
					print("</ul></td></tr>\n");
				}
				
				if ( ( $program && isset($program->description) && $program->description ) || ( isset($major->description) && $major->description ) )
				{
					print("		<tr><th>description: </th><td class=\"small\">");
					if ( $program && isset($program->description) && $program->description )
					{
						print(str_replace("\n", "<br> ", str_replace("\r\n", "<br> ", trim($program->description))) . "<br><br> ");
					}
					print(htmlspecialchars(trim($major->description), ENT_QUOTES|ENT_HTML5));
					print("</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td>");
				if ( $program && isset($program->code) && $program->code )
				{
					print("<a href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . generateLinkToProgramsAndCourses($program->code) . "</a><br> ");
				}
				print("<a href=\"" . generateLinkToProgramsAndCourses($major->code) . "\">" . generateLinkToProgramsAndCourses($major->code) . "</a>");
				print("</td></tr>\n");
				if ( !( $program && isset($program->name) ) && isset($major->programs) && $major->programs )
				{
					print("		<tr id=\"parentPrograms\"><th>parent program(s): </th><td class=\"small\" style=\"column-count: 2;\"><ul>\n");
					foreach ($major->programs as $program)
					{
						$program = getDefinition($program);
						if ( $program )
						{
							print("				<li><a href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("program", $program->code), array("major", $major->code)) . "\">" . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . "</a></li>\n");
						}
					}
					print("			</ul></td></tr>\n");
				}
				if ( ( isset($program->courses) && $program->courses ) || ( isset($major->courses) && $major->courses ) )
				{
					if ( $codeProgram && isset($program->courses) && $program->courses )
					{
						print("		<tr><th>courses in core: </th><td class=\"small\" style=\"column-count: 2;\"><ul>\n");
						foreach ($program->courses as $courseKey => $course)
						{
							$course = getCourse($course, $courseCodes, 'full');
							if ( $course )
							{
								print("				<li>");
								if ( $course->name )
								{
									print("<a href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $course->code)) . "\">" . $course->code . " — <span class=\"fst-italic\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</span></a>");
								}
								else
								{
									print("<a href=\"" . generateLinkToProgramsAndCourses($course->code) . "\">" . $course->code . "</a>");
								}
								if ( $accreditationDisplayScript && isset($program->coursesForAggregating) && in_array($course->code, $program->coursesForAggregating) )
								{
									print("<sup class=\"text-danger\">*</sup>");
								}
								print("</li>\n");
							}
							unset($program->courses[$courseKey]);
							$program->courses[$course->code] = $course;
						}
						print("			</ul>");
						if ( $accreditationDisplayScript )
						{
							print("<span class=\"small\"><sup class=\"text-danger\">*</sup> These courses are used to create the aggregate summaries below.</span>");
						}
						print("</td></tr>\n");
					}
					print("		<tr><th>courses in major: </th><td class=\"small\" style=\"column-count: 2;\"><ul>\n");
					foreach ($major->courses as $courseKey => $course)
					{
						$course = getCourse($course, $courseCodes, 'full');
						if ( $course )
						{
							print("				<li>");
							if ( $course->name )
							{
								print("<a href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $course->code)) . "\">" . $course->code . " — <span class=\"fst-italic\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</span></a>");
							}
							else
							{
								print("<a href=\"" . generateLinkToProgramsAndCourses($course->code) . "\">" . $course->code . "</a>");
							}
							if ( $accreditationDisplayScript )
							{
								print("<sup class=\"text-danger\">*</sup>");
							}
							print("</li>\n");
						}
						unset($major->courses[$courseKey]);
						$major->courses[$course->code] = $course;
					}
					print("			</ul></td></tr>\n");
					if ( isset($program->courses) && $program->courses )
					{
						foreach ($program->courses as $courseKey => $course)
						{
							if ( $course && isset($course->code) && (!isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) ) )
							{
								$major->courses[$courseKey] = $course;
							}
						}
					}
					// display aggregate assessment contributions
					if ( $accreditationDisplayScript )
					{
						$assessmentTypes = listAssessmentTypes();
						$assessmentTotals = 0.0;
						$assessmentCategorisationSummary = array();
						foreach ($major->courses as $courseKey => $course)
						{
							if ( isset($course->assessmentCategorisationSummary) && $course->assessmentCategorisationSummary )
							{
								$assessmentTotals += array_sum($course->assessmentCategorisationSummary);
								if ( !$assessmentCategorisationSummary )
								{
									$assessmentCategorisationSummary = $course->assessmentCategorisationSummary;
								}
								else
								{
									foreach ($course->assessmentCategorisationSummary as $assessmentKey => $assessmentValue)
									{
										$assessmentCategorisationSummary[$assessmentKey] += $assessmentValue;
									}
								}
							}
						}
						if ( $assessmentTotals > 0.0 )
						{
							print("		<tr><th>assessment: </th><td class=\"small\">");
							print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"fst-italic\">assessment types used across whole ");
							if ( $program && isset($program->name) )
							{
								print("degree");
							}
							else
							{
								print("major");
							}
							print("</caption>");
							print("<thead class=\"bg-light\"><tr><th>assessment type</th><th colspan=\"2\" class=\"text-center\">contribution to overall assessment</th></tr></thead><tbody>");
							foreach ($assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
							{
								if ( $assessmentTypeCredits > 0.0 )
								{
									$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
									if ( $assessmentTypePercentage == 0 )
									{
										$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 1);
									}
									print("<tr><td class=\"align-middle\">" . $assessmentTypes[$assessmentType]->type . "</td><td class=\"text-center align-middle\">" . $assessmentTypePercentage . "%</td><td class=\"col-6 align-middle\"><div class=\"progress bg-transparent\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width: " . $assessmentTypePercentage . "%\" aria-valuenow=\"" . $assessmentTypePercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $assessmentTypePercentage . "%\"></div></div></td></tr>");
								}
							}
							print("</tbody></table>");
							print("</td></tr>\n");
						}
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// display chart of development level learning against each of the competencies
				$majorCompetencies = array();
				$majorCompetencyName = "";
				$totalUnits = 0;	
				foreach ($major->courses as $courseKey => $course)
				{
					if ( !$majorCompetencyName && isset($course->competencyName) && $course->competencyName )
					{
						$majorCompetencyName = $course->competencyName;
					}
					$totalUnits += $course->units;
					if ( isset($course->competencies) && $course->competencies )
					{
						if ( !$majorCompetencies )
						{
							$majorCompetencies = $course->competencies;
						}
						else
						{
							foreach ($course->competencies as $competencyKey => $competency)
							{
								if ($majorCompetencies[$competencyKey]->competencyLevel < $competency->competencyLevel )
								{
									$majorCompetencies[$competencyKey]->competencyLevel = $competency->competencyLevel;
								}
							}
						}
					}
				}
				$bigProgramCompetencies = array();
				if ( isset($program->competencies) && $program->competencies )
				{
					foreach ($program->competencies as $competencyKey => $competency)
					{
						$bigProgramCompetencies[$competencyKey] = $competency;
					}
				}
								
				// display chart of progressive development towards EA competencies
				$majorMappingData = array();
				foreach ($major->courses as $courseKey => $course)
				{
					if ( isset($course->mappingData) && $course->mappingData )
					{
						$courseYear = 0 + substr($course->code, 4, 1);
						if ( $courseYear > 4 )
						{
							$courseYear = 4;
						}
						foreach ($course->mappingData as $competencyLabel => $DLs)
						{
							if ( !isset($majorMappingData[$competencyLabel]) )
							{
								$majorMappingData[$competencyLabel] = array(1 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 2 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 3 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 4 => array(1 => 0.0, 2 => 0.0, 3 => 0.0));
							}
							$majorMappingData[$competencyLabel][$courseYear][1] += $DLs[1];
							$majorMappingData[$competencyLabel][$courseYear][2] += $DLs[2];
							$majorMappingData[$competencyLabel][$courseYear][3] += $DLs[3];
						}
					}
				}
				$bigProgramMappingData = array();
				if ( isset($program->mappingData) && $program->mappingData )
				{
					$bigScale = 1;
					$majorUnits = 48;
					if ( $program->code == "AENGI" || $program->code == "AENRD") {
						$majorUnits -= 12;
					}
					if ( $totalUnits && $program->units && $totalUnits < ( $program->units + $majorUnits ) ) {
						$bigScale = $totalUnits / ( $program->units + $majorUnits );
					}
					foreach ($program->mappingData as $competencyLabel => $DLs)
					{
						if ( !isset($bigProgramMappingData[$competencyLabel]) )
						{
							$bigProgramMappingData[$competencyLabel] = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
						}
						$bigProgramMappingData[$competencyLabel][1] += $bigScale * $DLs[1];
						$bigProgramMappingData[$competencyLabel][2] += $bigScale * $DLs[2];
						$bigProgramMappingData[$competencyLabel][3] += $bigScale * $DLs[3];
					}
				}
				
				if ( $majorMappingData )
				{
					print("<section>\n");
					print("<h2>Cumulative annual progress towards the " . $majorCompetencyName . "</h2>\n");
					print("<div class=\"container\">\n");
					if ( $accreditationDisplayScript )
					{
						print("<p>This table maps how the unit credits across this ");
						if ( $program && isset($program->name) )
						{
							print("degree");
						}
						else
						{
							print("major");
						}
						print(" cumulatively contribute towards achievement of the " . $majorCompetencyName . ".</p>\n");
					}
					else
					{
						print("<p>This table depicts the relative cumulative contribution of this ");
						if ( $program && isset($program->name) )
						{
							print("degree");
						}
						else
						{
							print("major");
						}
						print(" towards the " . $majorCompetencyName . ". <em>Note that this illustration is indicative only, and does not include contributions from additional courses you may undertake</em>.</p>\n");
					}
					$maxUnits = 1;
					foreach ($majorMappingData as $competencyLabel => $years)
					{
						foreach ($years as $year => $DLs)
						{
							if ( 1 < $year )
							{
								$majorMappingData[$competencyLabel][$year][1] += $majorMappingData[$competencyLabel][$year-1][1];
								$majorMappingData[$competencyLabel][$year][2] += $majorMappingData[$competencyLabel][$year-1][2];
								$majorMappingData[$competencyLabel][$year][3] += $majorMappingData[$competencyLabel][$year-1][3];
							}
							$maxUnits = max($maxUnits, ceil(array_sum($majorMappingData[$competencyLabel][$year])));
						}
					}
					if ( $accreditationDisplayScript && $bigProgramMappingData )
					{
						foreach ($bigProgramMappingData as $competencyLabel => $DLs)
						{
							$maxUnits = max($maxUnits, ceil(array_sum($bigProgramMappingData[$competencyLabel])));
						}
					}
					print("<table class=\"table table-sm table-hover small\">\n");
					if ( $accreditationDisplayScript )
					{
						print("	<thead class=\"bg-light sticky-top\"><tr><th class=\"col-1\"></th><th class=\"text-left border-left\">0.0</th>");
						print("<th class=\"text-right border-right\">" . $maxUnits . ".0</th>");
						print("</tr></thead>\n");
					}
					print("	<tbody>\n");
					foreach ($majorCompetencies as $competencyKey => $competency)
					{
						if ( $competency->level == 1 )
						{
							print("		<tr class=\"table-secondary\"><td colspan=\"3\" class=\"fw-bold\">" . $competency->label . " " . $competency->text . "</td></tr>\n");
						}
						else if ( $competency->level == 2 )
						{
							print("		<tr class=\"border-bottom\"><td class=\"text-center align-middle border-right col-1\" data-toggle=\"tooltip\" data-placement=\"left\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</td><td colspan=\"2\" class=\"align-middle\">\n");
							foreach ($majorMappingData[$competency->label] as $year => $DLs)
							{
								print("			<div class=\"progress bg-transparent\">");
								if ( $accreditationDisplayScript )
								{
									if ( isset($majorMappingData[$competency->label][$year][1]) && $majorMappingData[$competency->label][$year][1] > 0.0)
									{
										$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][1] / $maxUnits;
										print("<div class=\"progress-bar bg-success text-left border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $majorMappingData[$competency->label][$year][1] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($majorMappingData[$competency->label][$year][1], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL1");
										}
										print("</div>");
									}
									if ( isset($majorMappingData[$competency->label][$year][2]) && $majorMappingData[$competency->label][$year][2] > 0.0)
									{
										$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][2] / $maxUnits;
										print("<div class=\"progress-bar bg-primary text-center border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $majorMappingData[$competency->label][$year][2] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($majorMappingData[$competency->label][$year][2], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL2");
										}
										print("</div>");
									}
									if ( isset($majorMappingData[$competency->label][$year][3]) && $majorMappingData[$competency->label][$year][3] > 0.0)
									{
										$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][3] / $maxUnits;
										print("<div class=\"progress-bar bg-danger text-right border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $majorMappingData[$competency->label][$year][3] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($majorMappingData[$competency->label][$year][3], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL3");
										}
										print("</div>");
									}
								}
								else
								{
									$sumOfUnits = array_sum($majorMappingData[$competency->label][$year]);
									if ( $sumOfUnits > 0.0)
									{
										$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
										print("<div class=\"progress-bar border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\"></div>");
									}
								}
								print("&nbsp;yr&nbsp;" . $year . "</div>\n");
							}
							if ( $accreditationDisplayScript && $bigProgramMappingData )
							{
								print("			<div class=\"progress bg-transparent\">");
								$sumOfUnits = array_sum($bigProgramMappingData[$competency->label]);
								$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
								print("<div class=\"progress-bar bg-secondary text-center border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $sumOfUnits . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($sumOfUnits, 2) . "\">");
								print("program</div>");
								print("</div>\n");
							}
							print("		</td></tr>\n");
						}
					}
					print("	</tbody>\n");
					print("</table>\n");
					print("</div>\n");
					print("</section>\n");
				}
				
				// list all EA competencies, and indicate which are addressed in this major
				if ( $majorCompetencies )
				{
					print("<section>\n");
					print("<h2>" . $majorCompetencyName . " — summary</h2>\n");
					if ( $accreditationDisplayScript && isset($bigProgramCompetencies) && $bigProgramCompetencies )
					{
						print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: the red ticks (<span class=\"text-danger\">✓</span>) represent the expectation of attainment of each competency as depicted via the program learning outcomes, and the green ticks (<span class=\"text-success\">✓</span>) represent attainment of each competency via aggregating from the courses.</div>\n");
					}
					print("<div class=\"container\">\n");
					print("<table class=\"table table-sm table-hover\">\n");
					print("	<tbody>\n");
					foreach ($majorCompetencies as $competencyKey => $competency)
					{
						switch ($competency->level)
						{
							case 1:
								print("		<tr class=\"table-secondary\">");
								$oneSpan = 5;
								if ( $accreditationDisplayScript && $bigProgramCompetencies)
								{
									$oneSpan++;
								}
								print("<th colspan=\"" . $oneSpan . "\">" . $competency->label . " " . $competency->text . "</th>");
								print("</tr>\n");
								break;
							case 2:
								print("		<tr class=\"small\">");
								if ( $accreditationDisplayScript && $bigProgramCompetencies)
								{
									if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
									{
										print("<td class=\"text-center text-danger align-middle\">");
										for ($i=0; $i<$bigProgramCompetencies[$competencyKey]->competencyLevel; $i++)
										{
											print("✓");
										}
										print("</td>");
									}
									else
									{
										print("<td></td>");
									}
								}
								if ( $competency->competencyLevel > 0 )
								{
									print("<td class=\"text-center text-success align-middle\">");
									if ( $accreditationDisplayScript )
									{
										for ($i=0; $i<$competency->competencyLevel; $i++)
										{
											print("✓");
										}
									}
									else
									{
										print("✓");
									}
									print("</td>");
								}
								else
								{
									print("<td></td>");
								}
								print("<td></td><td>" . $competency->label . "</td><td colspan=\"2\">" . $competency->text . "</td>");
								print("</tr>\n");
								break;
							case 3:
								if ( $accreditationDisplayScript )
								{
									print("		<tr class=\"small\">");
									if ( $bigProgramCompetencies)
									{
										if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
										{
											print("<td class=\"text-center text-danger align-middle\">");
											for ($i=0; $i<$bigProgramCompetencies[$competencyKey]->competencyLevel; $i++)
											{
												print("✓");
											}
											print("</td>");
										}
										else
										{
											print("<td></td>");
										}
									}
									if ( $competency->competencyLevel > 0 )
									{
										print("<td class=\"text-center text-success align-middle\">");
										if ( $accreditationDisplayScript )
										{
											for ($i=0; $i<$competency->competencyLevel; $i++)
											{
												print("✓");
											}
										}
										else
										{
											print("✓");
										}
										print("</td>");
									}
									else
									{
										print("<td></td>");
									}
									print("<td colspan=\"2\"></td><td>" . $competency->label . "</td><td>" . $competency->text . "</td>");
									print("</tr>\n");
								}
								break;
						}
					}
					print("	</tbody>\n");
					print("</table>\n");
					print("</div>\n");
					print("</section>\n");
				}
				
				break;










/*
	Display mapping information about the program
*/
			case 'program':
				print("<section>\n");
				print("<h2>" . $name . "</h2>\n");
				print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: information provided here is indicative only. For full and current information about this program view the official page on P&amp;C.</div>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>program: </th><td class=\"fst-italic\">" . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . "</td></tr>\n");
				if ($accreditationDisplayScript)
				{
					print("		<tr><th><i>JCUMap</i> files: </th><td><ul class=\"m-0\">");
					if ( file_exists("programs/" . $program->code . ".xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "programs/" . $program->code . ".xml\" download>" . $program->code . ".xml</a></li>");
					}
					if ( file_exists("programs/" . $program->code . "MappingResult.xml") )
					{
						print("<li><a href=\"" . $urlPrefix . "programs/" . $program->code . "MappingResult.xml\" download>" . $program->code . "MappingResult.xml</a></li>");
					}
					print("</ul></td></tr>\n");
				}
				if ( isset($program->description) && $program->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . str_replace("\n", "<br> ", str_replace("\r\n", "<br> ", trim($program->description))) . "</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . generateLinkToProgramsAndCourses($program->code) . "</a></td></tr>\n");
				if ( isset($program->majors) && $program->majors )
				{
					print("		<tr><th>majors: </th><td class=\"small\" style=\"column-count: 2;\"><ul>\n");
					foreach ($program->majors as $major)
					{
						$major = getDefinition($major);
						if ( $major )
						{
							print("				<li><a href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("program", $program->code), array("major", $major->code)) . "\">" . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . "</a></li>\n");
						}
					}
					print("			</ul></td></tr>\n");
				}
				if ( isset($program->courses) && $program->courses )
				{
					print("		<tr><th>courses in ");
					if ( isset($program->majors) && $program->majors )
					{
						print("core");
					}
					else
					{
						print("program");
					}
					print(": </th><td class=\"small\" style=\"column-count: 2;\"><ul>\n");
					foreach ($program->courses as $courseKey => $course)
					{
						$course = getCourse($course, $courseCodes, 'full');
						print("				<li>");
						if ( $course->name )
						{
							print("<a href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $course->code)) . "\">" . $course->code . " — <span class=\"fst-italic\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</span></a>");
						}
						else
						{
							print("<a href=\"" . generateLinkToProgramsAndCourses($course->code) . "\">" . $course->code . "</a>");
						}
						if ( $accreditationDisplayScript && ( !isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) ) )
						{
							print("<sup class=\"text-danger\">*</sup>");
						}
						print("</li>\n");
						$program->courses[$courseKey] = $course;
					}
					print("			</ul>");
					if ( $accreditationDisplayScript )
					{
						print("<span class=\"small\"><sup class=\"text-danger\">*</sup> These courses are used to create the aggregate summaries below.</span>");
					}
					print("</td></tr>\n");
				}
				// display aggregate assessment contributions
				if ( $accreditationDisplayScript )
				{
					$assessmentTypes = listAssessmentTypes();
					$assessmentTotals = 0.0;
					$assessmentCategorisationSummary = array();
					foreach ($program->courses as $courseKey => $course)
					{
						if ( !isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) )
						{
							if ( isset($course->assessmentCategorisationSummary) && $course->assessmentCategorisationSummary )
							{
								$assessmentTotals += array_sum($course->assessmentCategorisationSummary);
								if ( !$assessmentCategorisationSummary )
								{
									$assessmentCategorisationSummary = $course->assessmentCategorisationSummary;
								}
								else
								{
									foreach ($course->assessmentCategorisationSummary as $assessmentKey => $assessmentValue)
									{
										$assessmentCategorisationSummary[$assessmentKey] += $assessmentValue;
									}
								}
							}
						}
					}
					if ( $assessmentTotals > 0.0 )
					{
						print("		<tr><th>assessment: </th><td class=\"small\">");
						print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"fst-italic\">assessment types used across whole ");
						if ( isset($program->majors) && $program->majors )
						{
							print("core");
						}
						else
						{
							print("program");
						}
						print("</caption>");
						print("<thead class=\"bg-light\"><tr><th>assessment type</th><th colspan=\"2\" class=\"text-center\">contribution to overall assessment</th></tr></thead><tbody>");
						foreach ($assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
						{
							if ( $assessmentTypeCredits > 0.0 )
							{
								$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
								if ( $assessmentTypePercentage == 0 )
								{
									$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 1);
								}
								print("<tr><td class=\"align-middle\">" . $assessmentTypes[$assessmentType]->type . "</td><td class=\"text-center align-middle\">" . $assessmentTypePercentage . "%</td><td class=\"col-6 align-middle\"><div class=\"progress bg-transparent\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width: " . $assessmentTypePercentage . "%\" aria-valuenow=\"" . $assessmentTypePercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $assessmentTypePercentage . "%\"></div></div></td></tr>");
							}
						}
						print("</tbody></table>");
						print("</td></tr>\n");
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// display chart of development level learning against each of the competencies
				$programCompetencies = array();
				$programCompetencyName = "";
				$totalUnits = 0;
				foreach ($program->courses as $courseKey => $course)
				{
					if ( !isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) )
					{
						$totalUnits += $course->units;
						if ( !$programCompetencyName && isset($course->competencyName) && $course->competencyName )
						{
							$programCompetencyName = $course->competencyName;
						}
						if ( isset($course->competencies) && $course->competencies )
						{
							if ( !$programCompetencies )
							{
								$programCompetencies = $course->competencies;
							}
							else
							{
								foreach ($course->competencies as $competencyKey => $competency)
								{
									if ($programCompetencies[$competencyKey]->competencyLevel < $competency->competencyLevel )
									{
										$programCompetencies[$competencyKey]->competencyLevel = $competency->competencyLevel;
									}
								}
							}
						}
					}
				}
				$bigProgramCompetencies = array();
				if ( isset($program->competencies) && $program->competencies )
				{
					foreach ($program->competencies as $competencyKey => $competency)
					{
						$bigProgramCompetencies[$competencyKey] = $competency;
					}
				}
								
				// display chart of progressive development towards EA competencies
				$programMappingData = array();
				foreach ($program->courses as $courseKey => $course)
				{
					if ( ( !isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) ) && isset($course->mappingData) && $course->mappingData )
					{
						$courseYear = 0 + substr($course->code, 4, 1);
						if ( $courseYear > 4 )
						{
							$courseYear = 4;
						}
						foreach ($course->mappingData as $competencyLabel => $DLs)
						{
							if ( !isset($programMappingData[$competencyLabel]) )
							{
								$programMappingData[$competencyLabel] = array(1 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 2 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 3 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 4 => array(1 => 0.0, 2 => 0.0, 3 => 0.0));
							}
							$programMappingData[$competencyLabel][$courseYear][1] += $DLs[1];
							$programMappingData[$competencyLabel][$courseYear][2] += $DLs[2];
							$programMappingData[$competencyLabel][$courseYear][3] += $DLs[3];
						}
					}
				}
				$bigProgramMappingData = array();
				if ( isset($program->mappingData) && $program->mappingData )
				{
					$bigScale = 1;
					if ( $totalUnits && $program->units && $totalUnits < $program->units ) {
						$bigScale = $totalUnits / $program->units;
					}
					foreach ($program->mappingData as $competencyLabel => $DLs)
					{
						if ( !isset($bigProgramMappingData[$competencyLabel]) )
						{
							$bigProgramMappingData[$competencyLabel] = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
						}
						$bigProgramMappingData[$competencyLabel][1] += $bigScale * $DLs[1];
						$bigProgramMappingData[$competencyLabel][2] += $bigScale * $DLs[2];
						$bigProgramMappingData[$competencyLabel][3] += $bigScale * $DLs[3];
					}
				}
				
				if ( $programMappingData )
				{
					print("<section>\n");
					print("<h2>Program ");
					if ( isset($program->majors) && $program->majors )
					{
						print("core ");
					}
					print("cumulative contribution towards the " . $programCompetencyName . "</h2>\n");
					print("<div class=\"container\">\n");
					if ( $accreditationDisplayScript )
					{
						print("<p>This table maps how the unit credits across this program ");
						if ( isset($program->majors) && $program->majors )
						{
							print("core ");
						}
						print("cumulatively contribute towards achievement of the " . $programCompetencyName . ".</p>\n");
					}
					else
					{
						print("<p>This table depicts the relative cumulative contribution of this program ");
						if ( isset($program->majors) && $program->majors )
						{
							print("core ");
						}
						print("towards the " . $programCompetencyName . ". <em>Note that this illustration is indicative only, and does not take into account the contributions from any additional courses you may take (which includes majors, minors and specialisations)</em>.</p>\n");
					}
					
					$maxUnits = 1;
					foreach ($programMappingData as $competencyLabel => $years)
					{
						foreach ($years as $year => $DLs)
						{
							if ( 1 < $year )
							{
								$programMappingData[$competencyLabel][$year][1] += $programMappingData[$competencyLabel][$year-1][1];
								$programMappingData[$competencyLabel][$year][2] += $programMappingData[$competencyLabel][$year-1][2];
								$programMappingData[$competencyLabel][$year][3] += $programMappingData[$competencyLabel][$year-1][3];
							}
							$maxUnits = max($maxUnits, ceil(array_sum($programMappingData[$competencyLabel][$year])));
						}
					}
					if ( $accreditationDisplayScript && $bigProgramMappingData )
					{
						foreach ($bigProgramMappingData as $competencyLabel => $DLs)
						{
							$maxUnits = max($maxUnits, ceil(array_sum($bigProgramMappingData[$competencyLabel])));
						}
					}
					
					print("<table class=\"table table-sm table-hover small\">\n");
					if ( $accreditationDisplayScript )
					{
						print("	<thead class=\"bg-light sticky-top\"><tr><th class=\"col-1\"></th><th class=\"text-left border-left\">0.0</th>");
						print("<th class=\"text-right border-right\">" . $maxUnits . ".0</th>");
						print("</tr></thead>\n");
					}
					print("	<tbody>\n");
					foreach ($programCompetencies as $competencyKey => $competency)
					{
						if ( $competency->level == 1 )
						{
							print("		<tr class=\"table-secondary\"><td colspan=\"3\" class=\"fw-bold\">" . $competency->label . " " . $competency->text . "</td></tr>\n");
						}
						else if ( $competency->level == 2 )
						{
							print("		<tr class=\"border-bottom\"><td class=\"text-center align-middle border-right col-1\" data-toggle=\"tooltip\" data-placement=\"left\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</td><td colspan=\"2\" class=\"align-middle\">\n");
							foreach ($programMappingData[$competency->label] as $year => $DLs)
							{
								print("			<div class=\"progress bg-transparent\">");
								if ( $accreditationDisplayScript )
								{
									if ( isset($programMappingData[$competency->label][$year][1]) && $programMappingData[$competency->label][$year][1] > 0.0)
									{
										$mappingPercentage = 100 * $programMappingData[$competency->label][$year][1] / $maxUnits;
										print("<div class=\"progress-bar bg-success text-left border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $programMappingData[$competency->label][$year][1] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($programMappingData[$competency->label][$year][1], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL1");
										}
										print("</div>");
									}
									if ( isset($programMappingData[$competency->label][$year][2]) && $programMappingData[$competency->label][$year][2] > 0.0)
									{
										$mappingPercentage = 100 * $programMappingData[$competency->label][$year][2] / $maxUnits;
										print("<div class=\"progress-bar bg-primary text-center border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $programMappingData[$competency->label][$year][2] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($programMappingData[$competency->label][$year][2], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL2");
										}
										print("</div>");
									}
									if ( isset($programMappingData[$competency->label][$year][3]) && $programMappingData[$competency->label][$year][3] > 0.0)
									{
										$mappingPercentage = 100 * $programMappingData[$competency->label][$year][3] / $maxUnits;
										print("<div class=\"progress-bar bg-danger text-right border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $programMappingData[$competency->label][$year][3] . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($programMappingData[$competency->label][$year][3], 2) . "\">");
										if ( $year > 3 )
										{
											print("DL3");
										}
										print("</div>");
									}
								}
								else
								{
									$sumOfUnits = array_sum($programMappingData[$competency->label][$year]);
									if ( $sumOfUnits > 0.0)
									{
										$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
										print("<div class=\"progress-bar border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\"></div>");
									}
								}
								print("&nbsp;yr&nbsp;" . $year . "</div>\n");
							}
							if ( $accreditationDisplayScript && $bigProgramMappingData )
							{
								print("			<div class=\"progress bg-transparent\">");
								$sumOfUnits = array_sum($bigProgramMappingData[$competency->label]);
								$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
								print("<div class=\"progress-bar bg-secondary text-center border\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $sumOfUnits . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($sumOfUnits, 2) . "\">");
								print("program</div>");
								print("</div>\n");
							}
							print("		</td></tr>\n");
						}
					}
					print("	</tbody>\n");
					print("</table>\n");
					print("</div>\n");
					print("</section>\n");
				}
				
				// list all EA competencies, and indicate which are addressed in this program (core)
				if ( $programCompetencies )
				{
					print("<section>\n");
					print("<h2>" . $programCompetencyName . " — summary</h2>\n");
					if ( $accreditationDisplayScript && isset($bigProgramCompetencies) && $bigProgramCompetencies )
					{
						print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: the red ticks (<span class=\"text-danger\">✓</span>) represent the expectation of attainment of each competency as depicted via the program learning outcomes, and the green ticks (<span class=\"text-success\">✓</span>) represent attainment of each competency via aggregating from the courses.</div>\n");
					}
					print("<table class=\"table table-sm table-hover\">\n");
					print("	<tbody>\n");
					foreach ($programCompetencies as $competencyKey => $competency)
					{
						switch ($competency->level)
						{
							case 1:
								print("		<tr class=\"table-secondary\">");
								$oneSpan = 5;
								if ( $accreditationDisplayScript && $bigProgramCompetencies)
								{
									$oneSpan++;
								}
								print("<th colspan=\"" . $oneSpan . "\">" . $competency->label . " " . $competency->text . "</th>");
								print("</tr>\n");
								break;
							case 2:
								print("		<tr class=\"small\">");
								if ( $accreditationDisplayScript && $bigProgramCompetencies)
								{
									if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
									{
										print("<td class=\"text-center text-danger align-middle\">");
										for ($i=0; $i<$bigProgramCompetencies[$competencyKey]->competencyLevel; $i++)
										{
											print("✓");
										}
										print("</td>");
									}
									else
									{
										print("<td></td>");
									}
								}
								if ( $competency->competencyLevel > 0 )
								{
									print("<td class=\"text-center text-success align-middle\">");
									if ( $accreditationDisplayScript )
									{
										for ($i=0; $i<$competency->competencyLevel; $i++)
										{
											print("✓");
										}
									}
									else
									{
										print("✓");
									}
									print("</td>");
								}
								else
								{
									print("<td></td>");
								}
								print("<td></td><td>" . $competency->label . "</td><td colspan=\"2\">" . $competency->text . "</td>");
								print("</tr>\n");
								break;
							case 3:
								if ( $accreditationDisplayScript )
								{
									print("		<tr class=\"small\">");
									if ( $bigProgramCompetencies)
									{
										if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
										{
											print("<td class=\"text-center text-danger align-middle\">");
											for ($i=0; $i<$bigProgramCompetencies[$competencyKey]->competencyLevel; $i++)
											{
												print("✓");
											}
											print("</td>");
										}
										else
										{
											print("<td></td>");
										}
									}
									if ( $competency->competencyLevel > 0 )
									{
										print("<td class=\"text-center text-success align-middle\">");
										if ( $accreditationDisplayScript )
										{
											for ($i=0; $i<$competency->competencyLevel; $i++)
											{
												print("✓");
											}
										}
										else
										{
											print("✓");
										}
										print("</td>");
									}
									else
									{
										print("<td></td>");
									}
									print("<td colspan=\"2\"></td><td>" . $competency->label . "</td><td>" . $competency->text . "</td>");
									print("</tr>\n");
								}
								break;
						}
					}
					print("	</tbody>\n");
					print("</table>\n");
					print("</div>\n");
					print("</section>\n");
				}
				break;










/*
	Display mapping information about the FDD
*/
			case 'fdd':
				print("<section>\n");
				print("<h2>" . $name . "</h2>\n");
				print("<div class=\"alert alert-warning fst-italic\" role=\"alert\">Note: information provided here is indicative only. This program may not be an allowable FDD combination with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations and for full and current information about this degree, or consult with CECS Student Services.</div>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th><i>JCUMap</i> files: </th><td><ul class=\"m-0\">");
				if ( file_exists("programs/" . $fdd->fileName . ".xml") )
				{
					print("<li><a href=\"" . $urlPrefix . "programs/" . $fdd->fileName . ".xml\" download>" . $fdd->fileName . ".xml</a></li>");
				}
				if ( file_exists("programs/" . $fdd->fileName . "MappingResult.xml") )
				{
					print("<li><a href=\"" . $urlPrefix . "programs/" . $fdd->fileName . "MappingResult.xml\" download>" . $fdd->fileName . "MappingResult.xml</a></li>");
				}
				print("</ul></td></tr>\n");
				if ( isset($fdd->description) && $fdd->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . str_replace("\n", "<br> ", str_replace("\r\n", "<br> ", trim($fdd->description))) . "</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($fdd->code) . "\">" . generateLinkToProgramsAndCourses($fdd->code) . "</a></td></tr>\n");
				if ( isset($fdd->learningOutcomes) && $fdd->learningOutcomes )
				{
					print("		<tr><th>learning outcomes: </th><td class=\"small\"><ol>");
					foreach ($fdd->learningOutcomes as $learningOutcome)
					{
						if ( $learningOutcome )
						{
							print("<li>" . $learningOutcome . "</li>");
						}
					}
					print("</ol></td></tr>\n");
				}
				if ( isset($fdd->units) )
				{
					$unitContribution = number_format($fdd->units / 48, 1); // 48 units in one year
					print("		<tr><th>engineering contribution: </th><td>" . number_format($fdd->units, 1) . " units (" . $unitContribution . " ");
					if ( $unitContribution == 1 || $unitContribution == 1.00 )
					{
						print("year");
					}
					else
					{
						print("years");
					}
					print(")</td></tr>\n");
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// table of all learning outcomes mappings (to EA competencies and to assessment items)
				print("<section>\n");
				print("<h2>Mapped learning outcomes</h2>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-bordered table-hover small\">\n");
				print("	<colgroup>\n");
				print("		<col span=\"1\">\n");
				foreach ($fdd->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("		<col span=\"" . $competency->sublevels . "\">\n");
					}
				}
				print("	</colgroup>\n");
				print("	<thead class=\"bg-light\">\n");
				print("		<tr><th colspan=\"1\" rowspan=\"2\" class=\"text-center align-middle\">learning outcome</th>");
				foreach ($fdd->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("<th colspan=\"" . $competency->sublevels . "\" class=\"text-center\">" . $competency->label . " " . $competency->text . "</th>");
					}
				}
				print("</tr>\n");
				print("		<tr>");
				foreach ($fdd->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 2 )
					{
						print("<th class=\"text-center align-middle\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</th>");
					}
				}
				print("</tr>\n");
				print("	</thead>\n");
				print("	<tbody>\n");
				foreach ($fdd->learningOutcomes as $learningOutcomeN => $learningOutcome)
				{
					if ( $learningOutcome )
					{
						print("		<tr><td><ol class=\"m-0\" start=\"" . ( $learningOutcomeN + 1 ) . "\"><li>" . $learningOutcome . "</li></ol></td>");
						foreach ($fdd->competencies as $competencyKey => $competency)
						{
							if ( $competency->level == 2 )
							{
								print("<td class=\"text-center text-success align-middle\">");
								if ( isset($fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0 )
								{
									print("✓");
								}
								print("</td>");
							}
						}
						print("</tr>\n");
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// display chart of development level learning against each of the competencies
				print("<section>\n");
				print("<h2>Course contribution towards the " . $fdd->competencyName . "</h2>\n");
				if ( $accreditationDisplayScript )
				{
					print("<p>This table maps how the unit credits of this course contribute towards achievement of the " . $fdd->competencyName . ".</p>\n");
				}
				else
				{
					print("<p>This table depicts the relative contribution of this course towards the " . $fdd->competencyName . ". <em>Note that this illustration is indicative only, and may not take into account any recent changes to the course. You are advised to review the official course page on P&amp;C for current information.</em>.</p>\n");
				}
				$maxUnits = 1;
				foreach ($fdd->mappingData as $competencyLabel => $DLs)
				{
					$maxUnits = max($maxUnits, ceil(array_sum($DLs)));
				}
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-hover small\">\n");
				if ( $accreditationDisplayScript )
				{
					print("	<thead class=\"bg-light sticky-top\"><tr><th class=\"col-1\"></th><th class=\"text-left border-left\">0.0</th>");
					print("<th class=\"text-right border-right\">" . $maxUnits . ".0</th>");
					print("</tr></thead>\n");
				}
				print("	<tbody>\n");
				foreach ($fdd->competencies as $competencyKey => $competency)
				{
					if ( $competency->level == 1 )
					{
						print("		<tr class=\"table-secondary\"><td colspan=\"3\" class=\"fw-bold\">" . $competency->label . " " . $competency->text . "</td></tr>\n");
					}
					else if ( $competency->level == 2 )
					{
						print("		<tr class=\"border-bottom\"><td class=\"text-center align-middle border-right col-1\" data-toggle=\"tooltip\" data-placement=\"left\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</td><td colspan=\"2\" class=\"align-middle\">\n");
						print("			<div class=\"progress bg-transparent\">");
						$mappingSum = array_sum($fdd->mappingData[$competency->label]);
						if ( $mappingSum > 0.0)
						{
							$mappingPercentage = 100 * $mappingSum / $maxUnits;
							print("<div class=\"progress-bar bg-secondary\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $mappingSum . "\" aria-valuemin=\"0\" aria-valuemax=\"" . $maxUnits . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . number_format($mappingSum, 2) . "\"></div>");
						}
						print("</div>\n");
						print("		</td></tr>\n");
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				// list all EA competencies, and indicate which are addressed in this course
				print("<section>\n");
				print("<h2>" . $fdd->competencyName . " — summary</h2>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-hover\">\n");
				print("	<tbody>\n");
				foreach ($fdd->competencies as $competencyKey => $competency)
				{
					switch ($competency->level)
					{
						case 1:
							print("		<tr class=\"table-secondary\">");
							print("<th colspan=\"5\">" . $competency->label . " " . $competency->text . "</th>");
							print("</tr>\n");
							break;
						case 2:
							print("		<tr class=\"small\">");
							if ( $competency->competencyLevel > 0 )
							{
								print("<td class=\"text-center text-success align-middle\">✓</td>");
							}
							else
							{
								print("<td></td>");
							}
							print("<td></td><td>" . $competency->label . "</td><td colspan=\"2\">" . $competency->text . "</td>");
							print("</tr>\n");
							break;
					}
				}
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				print("</section>\n");
				
				break;
		}
	}
	else
	{
		echo '	' . resourceNotFound($type, $code) . PHP_EOL;
	}
	echo '	' . htmlPageFooter() . PHP_EOL;
	echo customJS('	', PHP_EOL);
	echo '</body>' . PHP_EOL;
}
else
{
	printDefaultLandingPage($programDefinitions, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript);
}
?>
</html>
