<!DOCTYPE html>
<html lang="en">
<?php
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);

require_once("./jcumap-output-processor.php");










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
	print("<h1 class=\"display-1\">CECS Professional Skills Mapping</h1>\n");
	
	if ( $code && $type && $name )
	{
		print("<h2>" . $name . "</h2>\n");
		print("<div class=\"container\">\n");
		switch ($type)
		{









/*
	Display mapping information about the course
*/
			case 'course':
				print("<table class=\"table\">\n");
				print("	<tbody>\n");
				print("		<tr><th>code: </th><td>" . $course->code . "</td></tr>\n");
				print("		<tr><th>name: </th><td class=\"font-italic\">" . $course->name . "</td></tr>\n");
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
				print("	</tbody>\n");
				print("</table>\n");
				// PUT AGGREGATED MAPPING FOR COURSE HERE
				break;









/*
	Display mapping information about the major
*/
			case 'major':
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
							print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./?code=" . $program->code . "\">" . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . "</a></li>\n");
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
						print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./?code=" . $course->code . "\">" . $course->code);
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
				break;









/*
	Display mapping information about the program
*/
			case 'program':
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
							print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./?code=" . $major->code . "\">" . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . " major</a></li>\n");
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
						print("				<li class=\"position-relative\"><a class=\"stretched-link\" href=\"./?code=" . $course->code . "\">" . $course->code);
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
				break;
		}
		print("</div>\n");
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
	print("<h1 class=\"display-1\">CECS Professional Skills Mapping</h1>\n");
	
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
				print("<a class=\"stretched-link\" href=\"./?code=" . $program->code . "\">" . $program->code . "</a>");
				print("</td><td class=\"position-relative\">");
				print("<a class=\"stretched-link\" href=\"./?code=" . $program->code . "\"><span class=\"text-decoration-underline\">engineering core</span>: " . htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . "</a>");
				print("</td><td class=\"text-center position-relative\">");
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
				print("</td></tr>\n");
				foreach ($program->majors as $majorCode)
				{
					foreach ($majors as $major)
					{
						if ( $major->code == $majorCode ) {
							print("		<tr><td class=\"small position-relative\">");
							print("<a class=\"stretched-link\" href=\"./?code=" . $major->code . "\">" . $major->code . "</a>");
							print("</td><td class=\"position-relative\">");
							print("<a class=\"stretched-link\" href=\"./?code=" . $major->code . "\"><span class=\"text-decoration-underline\">major</span>: " . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . "</a>");
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
	$courses = getAllCourseCodes();
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
			print("<a class=\"stretched-link\" href=\"./?code=" . $code . "\">" . $code . "</a>");
			print("</td><td class=\"position-relative\">");
			print("<a class=\"stretched-link font-italic\" href=\"./?code=" . $code . "\">");
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
