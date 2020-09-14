<!DOCTYPE html>
<html lang="en">
<?php
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);

require_once("./jcumap-output-processor.php");

$staffDisplayScript = '';
if ( isset($_GET['staffDisplay']) || ( isset($displayInformationForStaff) && $displayInformationForStaff) )
{
	$staffDisplayScript = 'staff.php';
}

	








/*
	Display information for the given program/major/course code
*/
if ( isset($_GET['code']) && strlen($_GET['code']) )
{
	$code = strtoupper(trim($_GET['code']));
	$name = "";
	$shortname = "";
	$type = false;
	$program = false;
	$major = false;
	$course = false;
	if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$type = 'course';
	}
	else if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == "-" )
	{
		$type = 'major';
	}
	else if ( strlen($code) == 5 && ctype_upper($code) )
	{
		$type = 'program';
	}
	else
	{
		$code = false;
	}
	switch ($type)
	{
		case 'course':
			$course = getCourse($code, 'full');
			if ( $course && $course->name )
			{
				$name = $code . " — <span class=\"font-italic\">" . $course->name . "</span>";
				$shortname = $code;
			}
			break;
		case 'major':
			$major = getDefinition($code);
			if ( $major && isset($major->name) )
			{
				$name = htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . " major";
				$shortname = $name;
			}
			break;
		case 'program':
			$program = getDefinition($code);
			if ( $program && isset($program->name) )
			{
				$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . " core";
				$shortname = htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . " (core)";
			}
			break;
	}
	if ( $code && $type )
	{
		
	} else {
		$code = false;
	}
	
	print("<head>\n");
	print("	<meta charset=\"utf-8\">\n");
	print("	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">\n");
	print("	<meta name=\"description\" content=\"CECS Professional Skills Mapping\">\n");
	print("	<meta name=\"author\" content=\"Rado Faletič\">\n");
	print("	<meta name=\"keywords\" content=\"CECS,EA,Engineers Australia,engineering,mapping\">\n");
	print("	<meta name=\"format-detection\" content=\"telephone=no\">\n\n");
		
	print("	<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css\" integrity=\"sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I\" crossorigin=\"anonymous\">\n\n");
		
	print("	<title>" . $shortname . " :: Professional Skills Mapping :: CECS :: ANU</title>\n");
	print("</head>\n");
	
	print("<body class=\"container\">\n");
	print("<h1 class=\"display-1 text-center\"><a class=\"text-reset\" href=\"./" . $staffDisplayScript . "\">CECS Professional Skills Mapping</a></h1>\n");
	
	if ( $code && $type && $name )
	{
		print("<h2>" . $name . "</h2>\n");
		switch ($type)
		{









/*
	Display mapping information about the course
*/
			case 'course':
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>code: </th><td>" . $course->code . "</td></tr>\n");
				print("		<tr><th>name: </th><td class=\"font-italic\">" . $course->name . "</td></tr>\n");
				if ( isset($course->units) )
				{
					print("		<tr><th>unit value: </th><td>" . $course->units . "</td></tr>\n");
				}
				if ( isset($course->description) && $course->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . str_replace("\n", "<br>\n", $course->description) . "</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($course->code) . "\">" . generateLinkToProgramsAndCourses($course->code) . "</a></td></tr>\n");
				if ( isset($course->learningOutcomes) && $course->learningOutcomes )
				{
					print("		<tr><th>learning outcomes: </th><td class=\"small\"><ol>");
					foreach ($course->learningOutcomes as $learningOutcome)
					{
						print("<li>" . $learningOutcome . "</li>");
					}
					print("</ol></td></tr>\n");
				}
				if ( isset($course->assessments) && $course->assessments )
				{
					print("		<tr><th>assessment: </th><td class=\"small\">");
					if ( $staffDisplayScript )
					{
						$assessmentTypes = listAssessmentTypes();
						print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"font-italic\">assessment breakdown</caption>");
						print("<colgroup><col span=\"2\"><col span=\"1\"><col span=\"1\"><col span=\"" . count($course->learningOutcomes) . "\"></colgroup>");
						print("<thead><tr><th colspan=\"2\" rowspan=\"2\">assessment item(s)</th><th rowspan=\"2\">category</th><th rowspan=\"2\" class=\"text-center\">weight</th><th colspan=\"" . count($course->learningOutcomes) . "\" class=\"text-center\">percentage breakdown (per 100% item)</th></tr><tr>");
						foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
						{
							print("<th class=\"text-center\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" title=\"" . ( $learningOutcomeN + 1 ). ". " . $learningOutcome . "\">" . ( $learningOutcomeN + 1 ). "</th>");
						}
						print("</tr></thead><tbody>");
						foreach ($course->assessments as $assessmentN => $assessment)
						{
							print("<tr><td>" . ( $assessmentN + 1 ) . ". </td><td>" . $assessment->name . "</td><td>" . $assessmentTypes[$assessment->typeCode]->type . "</td><td class=\"text-center font-weight-bold\">");
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
							print("<table class=\"table table-sm table-bordered table-hover caption-top\"><caption class=\"font-italic\">assessment types used across whole subject</caption>");
							print("<thead><tr><th>assessment type</th><th colspan=\"2\" class=\"text-center\">contribution to overall assessment</th></tr></thead><tbody>");
							foreach ($course->assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
							{
								if ( $assessmentTypeCredits > 0.0 )
								{
									$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
									print("<tr><td class=\"align-middle\">" . $assessmentTypes[$assessmentType]->type . "</td><td class=\"text-center align-middle\">" . $assessmentTypePercentage . "%</td><td class=\"col-6 align-middle\"><div class=\"progress\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width: " . $assessmentTypePercentage . "%\" aria-valuenow=\"" . $assessmentTypePercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\"></div></div></td></tr>");
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
				
				// table of all learning outcomes mappings (to EA competencies and to assessment items)
				print("<h2>Mapped learning outcomes</h2>\n");
				print("<div class=\"container\">\n");
				print("<table class=\"table table-sm table-bordered table-hover small\">\n");
				print("	<colgroup>\n");
				print("		<col span=\"2\">\n");
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
				print("	<thead>\n");
				print("		<tr><th colspan=\"2\" rowspan=\"2\" class=\"text-center align-middle\">learning outcome</th>");
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
						if ( $staffDisplayScript )
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
					print("		<tr><td>" . ( $learningOutcomeN + 1 ) . ". </td><td class=\"small\">" . $learningOutcome . "</td>");
					foreach ($course->competencies as $competencyKey => $competency)
					{
						if ( $competency->level == 2 )
						{
							print("<td class=\"text-center text-success align-middle\">");
							if ( isset($course->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $course->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0)
							{
								if ( $staffDisplayScript )
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
							if ( $staffDisplayScript )
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
				print("	</tbody>\n");
				print("</table>\n");
				print("</div>\n");
				
				// display chart of development level learning against each of the competencies
				if ( $staffDisplayScript )
				{
					print("<h2>Mapping of credit points across learning outcomes</h2>\n");
					print("<div class=\"container\">\n");
					print("<table class=\"table table-sm small\">\n");
					print("	<thead><tr><th class=\"col-1\"></th><th class=\"text-left\">0.0</th>");
					$maxUnits = 1;
					foreach ($course->mappingData as $competencyLabel => $DLs)
					{
						if ( $DLs[1] > $maxUnits )
						{
							$maxUnits = ceil($DLs[1]);
						}
						if ( $DLs[2] > $maxUnits )
						{
							$maxUnits = ceil($DLs[2]);
						}
						if ( $DLs[3] > $maxUnits )
						{
							$maxUnits = ceil($DLs[3]);
						}
					}
					print("<th class=\"text-right\">" . $maxUnits . ".0</th>");
					print("</tr></thead>\n");
					print("	<tbody>\n");
					foreach ($course->competencies as $competencyKey => $competency)
					{
						if ( $competency->level == 1 )
						{
							print("		<tr class=\"table-secondary\"><td colspan=\"" . ( $maxUnits + 2) . "\" class=\"font-weight-bold small\">" . $competency->label . " " . $competency->text . "</td></tr>\n");
						}
						else if ( $competency->level == 2 )
						{
							print("		<tr><td rowspan=\"3\" class=\"text-center align-middle\" data-toggle=\"tooltip\" data-placement=\"left\" data-html=\"true\" title=\"" . $competency->text . "\">" . $competency->label . "</td><td colspan=\"" . ( $maxUnits + 1 ). "\" class=\"align-middle\"><div class=\"progress\">");
							if ( isset($course->mappingData[$competency->label][1]) && $course->mappingData[$competency->label][1] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][1] / $maxUnits;
								print("<div class=\"progress-bar bg-success text-left\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $mappingPercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\">DL1</div>");
							}
							else
							{
								print("DL1");
							}
							print("</div></td></tr>\n");
							print("		<tr><td colspan=\"" . ( $maxUnits + 1 ). "\" class=\"align-middle\"><div class=\"progress\">");
							if ( isset($course->mappingData[$competency->label][2]) && $course->mappingData[$competency->label][2] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][2] / $maxUnits;
								print("<div class=\"progress-bar bg-primary text-left\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $mappingPercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\">DL2</div>");
							}
							else
							{
								print("<div class=\"small\">DL2</div>");
							}
							print("</div></td></tr>\n");
							print("		<tr><td colspan=\"" . ( $maxUnits + 1 ). "\"><div class=\"progress\">");
							if ( isset($course->mappingData[$competency->label][3]) && $course->mappingData[$competency->label][3] > 0.0)
							{
								$mappingPercentage = 100 * $course->mappingData[$competency->label][3] / $maxUnits;
								print("<div class=\"progress-bar bg-danger text-left\" role=\"progressbar\" style=\"width: " . $mappingPercentage . "%\" aria-valuenow=\"" . $mappingPercentage . "\" aria-valuemin=\"0\" aria-valuemax=\"100\">DL3</div>");
							}
							else
							{
								print("<div class=\"small\">DL3</div>");
							}
							print("</div></td></tr>\n");
						}
					}
					print("	</tbody>\n");
					print("</table>\n");
					print("</div>\n");
				}
				
				// list all EA competencies, and indicate which are addressed in this course
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
							if ( $competency->competencyLevel > 0 )
							{
								print("<th class=\"text-center text-success align-middle\">");
								if ( $staffDisplayScript )
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
								print("</th>");
							}
							else
							{
								print("<th></th>");
							}
							print("<th colspan=\"2\">" . $competency->label . "</th><th colspan=\"2\">" . $competency->text . "</th>");
							print("</tr>\n");
							break;
						case 2:
							print("		<tr class=\"small\">");
							if ( $competency->competencyLevel > 0 )
							{
								print("<td class=\"text-center text-success align-middle\">");
								if ( $staffDisplayScript )
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
							if ($staffDisplayScript)
							{
								print("		<tr class=\"small\">");
								if ( $competency->competencyLevel > 0 )
								{
									print("<td class=\"text-center text-success align-middle\">");
									if ( $staffDisplayScript )
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
				
				break;









/*
	Display mapping information about the major
*/
			case 'major':
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>major: </th><td class=\"font-italic\">" . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . " major</td></tr>\n");
				if ( isset($major->description) && $major->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . htmlspecialchars($major->description, ENT_QUOTES|ENT_HTML5) . "</td></tr>\n");
				}
				if ( isset($major->school) && $major->school )
				{
					print("		<tr><th>school: </th><td>" . htmlspecialchars($major->school, ENT_QUOTES|ENT_HTML5) . "</td></tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($major->code) . "\">" . generateLinkToProgramsAndCourses($major->code) . "</a></td></tr>\n");
				if ( isset($major->programs) && $major->programs )
				{
					print("		<tr><th>part of programs: </th><td class=\"small\"><ul>\n");
					foreach ($major->programs as $program)
					{
						$program = getDefinition($program);
						if ( $program )
						{
							print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $program->code . "\">" . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . "</a></li>\n");
						}
					}
					print("			</ul></td></tr>\n");
				}
				if ( isset($major->courses) && $major->courses )
				{
					print("		<tr><th>courses in major: </th><td class=\"small\"><ul>\n");
					foreach ($major->courses as $course)
					{
						$course = getCourse($course, 'basic');
						print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $course->code . "\">" . $course->code);
						if ( $course->name )
						{
							print(" — <span class=\"font-italic\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</span>");
						}
						print("</a></li>\n");
					}
					print("			</ul></td></tr>\n");
				}
				print("	</tbody>\n");
				print("</table>\n");
				// PUT AGGREGATED MAPPING FOR MAJOR HERE
				print("</div>\n");
				break;









/*
	Display mapping information about the program
*/
			case 'program':
				print("<div class=\"container\">\n");
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>program: </th><td class=\"font-italic\">" . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . "</td></tr>\n");
				if ( isset($program->description) && $program->description )
				{
					print("		<tr><th>description: </th><td class=\"small\">" . htmlspecialchars($program->description, ENT_QUOTES|ENT_HTML5) . "</td></tr>\n");
				}
				if ( isset($program->school) && $program->school )
				{
					print("		<tr><th>school: </th>");
					if ( isset($program->schoolWebsite) && $program->schoolWebsite )
					{
						print("<td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . htmlspecialchars($program->schoolWebsite, ENT_QUOTES|ENT_HTML5) . "\">" . htmlspecialchars($program->school, ENT_QUOTES|ENT_HTML5) . "</a></td>");
					}
					else
					{
						print("<td>" . htmlspecialchars($program->school, ENT_QUOTES|ENT_HTML5) . "</td>");
					}
					print("</tr>\n");
				}
				print("		<tr><th>P&amp;C: </th><td class=\"position-relative\"><a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . generateLinkToProgramsAndCourses($program->code) . "</a></td></tr>\n");
				if ( isset($program->majors) && $program->majors )
				{
					print("		<tr><th>majors: </th><td class=\"small\"><ul>\n");
					foreach ($program->majors as $major)
					{
						$major = getDefinition($major);
						if ( $major )
						{
							print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $major->code . "\">" . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . " major</a></li>\n");
						}
					}
					print("			</ul></td></tr>\n");
				}
				if ( isset($program->courses) && $program->courses )
				{
					print("		<tr><th>courses in core: </th><td class=\"small\"><ul>\n");
					foreach ($program->courses as $course)
					{
						$course = getCourse($course, 'basic');
						print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $course->code . "\">" . $course->code);
						if ( $course->name )
						{
							print(" — <span class=\"font-italic\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</span>");
						}
						print("</a></li>\n");
					}
					print("			</ul></td></tr>\n");
				}
				print("	</tbody>\n");
				print("</table>\n");
				// PUT AGGREGATED MAPPING FOR CORE HERE
				print("</div>\n");
				break;
		}
	}
	else
	{
		print('<div class="alert alert-danger" role="alert"><h3 class="alert-heading">Error</h3><p>');
		switch ($type)
		{
			case 'course':
				print("Could not find information for course “" . $code . "”.");
				break;
			case 'major':
				print("Could not find information for major “" . $code . "”.");
				break;
			case 'program':
				print("Could not find information for program “" . $code . "”.");
				break;
			default:
				print("Code information incorrect. Please go back and try again.");
				break;
		}
		print('</p></div>' . "\n");
	}
	print("<script src=\"https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js\" integrity=\"sha384-DBjhmceckmzwrnMMrjI7BvG2FmRuxQVaTfFYHgfnrdfqMhxKt445b7j3KBQLolRl\" crossorigin=\"anonymous\"></script>\n");
	print("<script>var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle=\"tooltip\"]')); var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); })</script>\n");
	print("</body>\n");
}
else
{










/*
	Default landing page, with summary information about all programs
*/
	print("<head>\n");
	print("	<meta charset=\"utf-8\">\n");
	print("	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">\n");
	print("	<meta name=\"description\" content=\"CECS Professional Skills Mapping\">\n");
	print("	<meta name=\"author\" content=\"Rado Faletič\">\n");
	print("	<meta name=\"keywords\" content=\"CECS,EA,Engineers Australia,engineering,mapping\">\n");
	print("	<meta name=\"format-detection\" content=\"telephone=no\">\n\n");
		
	print("	<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css\" integrity=\"sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I\" crossorigin=\"anonymous\">\n\n");
		
	print("	<title>Professional Skills Mapping :: CECS :: ANU</title>\n");
	print("</head>\n");
	
	print("<body class=\"container\">\n");
	print("<h1 class=\"display-1 text-center\"><a class=\"text-reset\" href=\"./" . $staffDisplayScript . "\">CECS Professional Skills Mapping</a></h1>\n");
	
	print("<h2>Mapped degree programs &amp; majors</h2>\n");
	
	// get the programs
	$programs = array();
	$programDefinitions = getDefinition("programs");
	if (isset($programDefinitions) && count($programDefinitions))
	{
		foreach ($programDefinitions as $program)
		{
			$program = getDefinition($program);
			if ( isset($program) && isset($program->name) )
			{
				$programs[$program->code] = $program;
			}
		}
	}
	
	// get the majors
	$majors = array();
	$majorDefinitions = getDefinition("majors");
	if ( isset($majorDefinitions) && count($majorDefinitions) )
	{
		foreach ($majorDefinitions as $major)
		{
			$major = getDefinition($major);
			if ( isset($major) && isset($major->name) )
			{
				$majors[$major->code] = $major;
			}
		}
	}
	
	print("<div class=\"container\">\n");
	if ( $programs ) {
		print("<table class=\"table table-sm table-hover\">\n");
		print("	<thead>\n");
		print("		<tr><th class=\"small\">code</th><th>name</th><th class=\"text-center\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($programs as $program)
		{
			print("		<tr class=\"table-secondary\"><td class=\"small\"></td><td class=\"h3\">");
			print($program->name);
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
			if ( isset($program->majors) && !empty($program->majors) )
			{
				print("		<tr><td class=\"small position-relative\">");
				print("<a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $program->code . "\">" . $program->code . "</a>");
				print("</td><td class=\"position-relative\">");
				print("<a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $program->code . "\"><span class=\"text-decoration-underline\">engineering core</span>: " . htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . "</a>");
				print("</td><td class=\"text-center position-relative\">");
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
				print("</td></tr>\n");
				foreach ($program->majors as $majorCode)
				{
					foreach ($majors as $major)
					{
						if ( $major->code == $majorCode ) {
							print("		<tr><td class=\"small position-relative\">");
							print("<a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $major->code . "\">" . $major->code . "</a>");
							print("</td><td class=\"position-relative\">");
							print("<a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $major->code . "\"><span class=\"text-decoration-underline\">major</span>: " . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . "</a>");
							print("</td><td class=\"text-center position-relative\">");
							print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($major->code) . "\">" . biBoxArrowUpRight() . "</a>");
							print("</td></tr>\n");
						}
					}
				}
			}
		}
		print("	</tbody>\n");
		print("</table>\n");
	} else {
		print('<div class="alert alert-danger" role="alert"><h3 class="alert-heading">Error</h3><p>Could not load programs and majors.</p></div>' . "\n");
	}
	print("</div>\n");
	
	print("<h2>Complete list of mapped courses</h2>\n");
	$courses = listAllCourseCodes();
	print("<div class=\"container\">\n");
	if ( $courses )
	{
		print("<table class=\"table table-sm table-hover\">\n");
		print("	<thead>\n");
		print("		<tr><th class=\"small\">code</th><th>name</th><th class=\"text-center\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($courses as $code)
		{
			print("		<tr><td class=\"small position-relative\">");
			print("<a class=\"stretched-link\" href=\"./" . $staffDisplayScript . "?code=" . $code . "\">" . $code . "</a>");
			print("</td><td class=\"position-relative\">");
			print("<a class=\"stretched-link font-italic\" href=\"./" . $staffDisplayScript . "?code=" . $code . "\">");
			$course = getCourse($code, 'basic');
			if ($course)
			{
				print(htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5));
			}
			print("</a>");
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
		}
		print("	</tbody>\n");
		print("</table>\n");
	} else {
		print('<div class="alert alert-warning" role="alert"><h3 class="alert-warning">Notice</h3><p>Could not find any course mappings.</p></div>' . "\n");
	}
	print("</div>\n");
	print("</body>\n");
}
?>
</html>
