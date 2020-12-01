<?php
/*
	html-processor.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/





/* This function returns an SVG string that represent an "external link" icon,
	“Box arrow up-right” from the Bootstrap icons https://icons.getbootstrap.com/icons/box-arrow-up-right/ */
function biBoxArrowUpRight()
{
	return '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-box-arrow-up-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>';
}





/* This function generates an internal URL based on given configuration parameters */
function createLink($display = "pretty", $prefix = "/", $base = "index.php", $q1 = array(), $q2 = array())
{
	$url = "";
	switch ($display)
	{
		case "pretty":
			$url = $prefix;
			if ( $base != "index.php" &&  substr($base, -4) == ".php" )
			{
				$url .= substr($base, 0, -4);
			}
			if ( $q1 & count($q1) == 2 )
			{
				if ( substr($url, -1) != "/" )
				{
					$url .= "/";
				}
				if ( $q1[0] == "fdd" )
				{
					$url .= "FDD-";
				}
				$url .= $q1[1];
				if ( $q2 && count($q1) == 2 )
				{
					$url .= "/" . $q2[1];
				}
			}
			break;
		case "php":
			$url = $prefix;
			if ( $base != "index.php" )
			{
				$url .= $base;
			}
			if ( $q1 && count($q1) == 2 )
			{
				$url .= "?" . $q1[0] . "=" . $q1[1];
				if ( $q2 && count($q2) == 2)
				{
					$url .= "&amp;" . $q2[0] . "=" . $q2[1];
				}
			}
			break;
	}
	return $url;
}





/* This function generates an external URL to the Programs & Courses website based solely on the given code */
function generateLinkToProgramsAndCourses($code)
{
	$urlPrefix = "https://programsandcourses.anu.edu.au";
	$programPrefix = "/program";
	$majorPrefix = "/major";
	$coursePrefix = "/course";
	
	// check if code is a course, major, or program
	$code = trim($code);
	$url = $urlPrefix;
	if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$url .= $coursePrefix . "/" . $code;
	}
	else if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == "-" )
	{
		$url .= $majorPrefix . "/" . $code;
	}
	else if ( 3 <= strlen($code) && ctype_upper($code) )
	{
		$url .= $programPrefix . "/" . $code;
	}
	return $url;
}





/* this function generates the default landing page */
function defaultLandingPage($programDefinitions, $urlDisplayType = "pretty", $urlPrefix = "/", $urlScript = "index.php", $accreditationDisplayScript = "")
{
	print("<head>\n");
	print("	<meta charset=\"utf-8\">\n");
	print("	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">\n");
	print("	<meta name=\"description\" content=\"CECS Professional Skills Mapping\">\n");
	print("	<meta name=\"author\" content=\"Rado Faletič\">\n");
	print("	<meta name=\"keywords\" content=\"CECS,EA,Engineers Australia,engineering,mapping\">\n");
	print("	<meta name=\"format-detection\" content=\"telephone=no\">\n\n");
		
	print("	<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-CuOF+2SnTUfTwSZjCXf01h7uYhfOBuxIhGKPbfEJ3+FqH/s6cIFN9bGr1HmAg4fQ\" crossorigin=\"anonymous\">\n\n");
		
	print("	<title>Professional Skills Mapping :: CECS :: ANU</title>\n");
	print("</head>\n");
	
	print("<body class=\"container\">\n");
	print("<h1 class=\"display-1 text-center\"><a class=\"text-reset\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript) . "\">CECS Professional Skills Mapping</a></h1>\n");
	print("<div class=\"alert alert-info fst-italic\" role=\"alert\">Note: information provided here is indicative only. For full and current information view the official pages on P&amp;C.</div>\n");
	
	print("<section id=\"programs\">\n");
	print("<h2>Mapped degree programs &amp; majors</h2>\n");
	
	// get the programs
	$programs = array();
	$majors = array();
	if (isset($programDefinitions) && count($programDefinitions))
	{
		foreach ($programDefinitions as $program)
		{
			$program = getDefinition($program);
			if ( isset($program) && isset($program->name) )
			{
				$programs[$program->code] = $program;
				if ( $program->majors )
				{
					foreach ($program->majors as $majorCode)
					{
						$major = getDefinition($majorCode);
						if ( isset($major) && isset($major->name) )
						{
							$majors[$major->code] = $major;
						}
					}
				}
			}
		}
	}
	
	print("<div class=\"container\">\n");
	if ( $programs ) {
		print("<table class=\"table table-sm table-hover\">\n");
		print("	<thead class=\"bg-light sticky-top\">\n");
		print("		<tr><th class=\"small col-2\">code</th><th>name</th><th class=\"text-center col-1\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($programs as $program)
		{
			print("		<tr class=\"table-secondary\"><td class=\"small\"></td><td class=\"h3\">");
			print($program->name);
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
			print("		<tr><td class=\"small position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("program", $program->code)) . "\">" . $program->code . "</a>");
			print("</td><td class=\"position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("program", $program->code)) . "\">");
			if ( $program->majors )
			{
				print("<span class=\"text-decoration-underline\">engineering core</span>: ");
			}
			print(htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . "</a>");
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
			if ( $program->majors )
			{
				foreach ($program->majors as $majorCode)
				{
					foreach ($majors as $major)
					{
						if ( $major->code == $majorCode )
						{
							print("		<tr><td class=\"small position-relative\">");
							print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . "\">" . $major->code . "</a>");
							print("</td><td class=\"position-relative\">");
							print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . "\"><span class=\"text-decoration-underline\">major</span>: " . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . "</a>");
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
	print("</section>\n");
	
	print("<section id=\"courses\">\n");
	print("<h2>Mapped courses</h2>\n");
	$courses = listAllCourseCodes();
	$engnCourses = array();
	foreach ($courses as $courseKey => $courseFile)
	{
		if ( substr($courseKey, 0, 4) == "ENGN" )
		{
			$engnCourses[$courseKey] = $courseFile;
		}
	}
	$compCourses = array();
	foreach ($courses as $courseKey => $code)
	{
		if ( substr($courseKey, 0, 4) == "COMP" )
		{
			$compCourses[$courseKey] = $courseFile;
		}
	}
	$otherCourses = array();
	foreach ($courses as $courseKey => $courseFile)
	{
		if ( substr($courseKey, 0, 4) != "ENGN" && substr($courseKey, 0, 4) != "COMP" )
		{
			$otherCourses[$courseKey] = $courseFile;
		}
	}
	if ( $engnCourses )
	{
		print("<div class=\"container\" id=\"ENGN\">\n");
		print("<table class=\"table table-sm table-hover caption-top\">\n");
		print("	<caption class=\"h3\">Engineering</caption>\n");
		print("	<thead class=\"bg-light sticky-top\">\n");
		print("		<tr><th class=\"small col-2\">code</th><th>name</th><th class=\"text-center col-1\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($engnCourses as $code => $courseFile)
		{
			$course = getCourse($code, 'basic');
			print("		<tr><td class=\"small position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . $code . "</a>");
			}
			else
			{
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . $code . "</a>");
			}
			print("</td><td class=\"position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link fst-italic\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</a>");
			}
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
		}
		print("	</tbody>\n");
		print("</table>\n");
		print("</div>\n");
	}
	if ( $compCourses )
	{
		print("<div class=\"container\" id=\"COMP\">\n");
		print("<table class=\"table table-sm table-hover caption-top\">\n");
		print("	<caption class=\"h3\">Computing</caption>\n");
		print("	<thead class=\"bg-light sticky-top\">\n");
		print("		<tr><th class=\"small col-2\">code</th><th>name</th><th class=\"text-center col-1\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($compCourses as $code => $courseFile)
		{
			$course = getCourse($code, 'basic');
			print("		<tr><td class=\"small position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . $code . "</a>");
			}
			else
			{
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . $code . "</a>");
			}
			print("</td><td class=\"position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link fst-italic\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</a>");
			}
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
		}
		print("	</tbody>\n");
		print("</table>\n");
		print("</div>\n");
	}
	if ( !$engnCourses && !$compCourses )
	{
		print('<div class=\"container\"><div class="alert alert-danger" role="alert"><h3 class="alert-danger">Error</h3><p>Could not find any ENGN or COMP course mappings.</p></div></div>' . "\n");
	}
	if ( $otherCourses )
	{
		print("<div class=\"container\" id=\"other\">\n");
		print("<table class=\"table table-sm table-hover caption-top\">\n");
		print("	<caption class=\"h3\">Other</caption>\n");
		print("	<thead class=\"bg-light sticky-top\">\n");
		print("		<tr><th class=\"small col-2\">code</th><th>name</th><th class=\"text-center col-1\">P&amp;C</th></tr>\n");
		print("	</thead>\n");
		print("	<tbody>\n");
		foreach ($otherCourses as $code => $courseFile)
		{
			$course = getCourse($code, 'basic');
			print("		<tr><td class=\"small position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . $code . "</a>");
			}
			else
			{
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . $code . "</a>");
			}
			print("</td><td class=\"position-relative\">");
			if ( $course->name )
			{
				print("<a class=\"stretched-link fst-italic\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . "\">" . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . "</a>");
			}
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
		}
		print("	</tbody>\n");
		print("</table>\n");
		print("</div>\n");
	}
	print("</section>\n");
	
	if ( $accreditationDisplayScript )
	{
		print("<section id=\"fdd\">\n");
		print("<h2>Mapped non-engineering degree programs (for <abbr title=\"Flexible Double Degree\">FDD</abbr>s)</h2>\n");
		print("<div class=\"alert alert-warning\">Note: not all of these programs are allowable FDD combinations with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations, or consult with CECS Student Services.</div>\n");
		$fdds = listAllPrograms();
		if ( $fdds )
		{
			$fddPrograms = array();
			foreach ($fdds as $code)
			{
				$fdd = getFDD($code, 'basic');
				if ( !in_array($code, $programDefinitions) && $fdd->code == $code && $fdd->name )
				{
					$fddPrograms[$code] = $fdd->name;
				}
			}
			asort($fddPrograms);
			
			
			print("<div class=\"container\">\n");
			print("<table class=\"table table-sm table-hover caption-top\">\n");
			print("	<thead class=\"bg-light sticky-top\">\n");
			print("		<tr><th class=\"small col-2\">code</th><th>name</th><th class=\"text-center col-1\">eng. years</th><th class=\"text-center col-1\">P&amp;C</th></tr>\n");
			print("	</thead>\n");
			print("	<tbody>\n");
			foreach ($fddPrograms as $code => $name)
			{
				$fdd = getFDD($code, 'full');
				print("		<tr><td class=\"small position-relative\">");
				print("<a class=\"stretched-link\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . "\">" . $code . "</a>");
				print("</td><td class=\"position-relative\">");
				print("<a class=\"stretched-link fst-italic\" href=\"" . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . "\">" . htmlspecialchars($name, ENT_QUOTES|ENT_HTML5) . "</a>");
				print("</td><td class=\"text-center\">" . number_format($fdd->units / 48, 1) . "</th><td class=\"text-center position-relative\">");
				print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($code) . "\">" . biBoxArrowUpRight() . "</a>");
				print("</td></tr>\n");
			}
			print("	</tbody>\n");
			print("</table>\n");
			print("</div>\n");
		}
		else
		{
			print('<div class=\"container\"><div class="alert alert-warning" role="alert"><h3 class="alert-warning">Notice</h3><p>Could not find any degree mappings.</p></div></div>' . "\n");
		}
		print("</section>\n");
	}
	
	
	print("</body>\n");
}