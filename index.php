<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="CECS Professional Skills Mapping">
	<meta name="author" content="Rado Faletič">
	<meta name="keywords" content="CECS,EA,Engineers Australia,engineering,mapping">
	<meta name="format-detection" content="telephone=no">
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
	
	<title>ANU :: CECS Professional Skills Mapping</title>
</head>

<body class="container">
<h1 class="display-1">CECS Professional Skills Mapping</h1>

<h2>Degree programs &amp; majors</h2>
<?php
require_once("./jcumap-output-processor.php");

$programDefinitions = getDefinition("./definitions/definitions.json");

// get the programs
$programs = array();
if (isset($programDefinitions->programs) && count($programDefinitions->programs)) {
	foreach ($programDefinitions->programs as $program) {
		$program = getDefinition("./definitions/" . $program . ".json");
		if (isset($program->program) && strlen($program->program)) {
			$programs[$program->code] = $program;
		}
	}
}

// get the majors
$majors = false;
if (isset($programDefinitions->majors) && count($programDefinitions->majors)) {
	$majors = $programDefinitions->majors;
}

if ($programs) {
	print("<table class=\"table\">\n");
	print("	<thead>\n");
	print("		<tr><th class=\"small\">code</th><th>name</th><th class=\"text-center\">P&amp;C</th></tr>\n");
	print("	</thead>\n");
	print("	<tbody>\n");
	foreach ($programs as $program) {
		print("		<tr class=\"table-secondary\"><td class=\"small\"></td><td class=\"h3\">");
		print($program->name);
		print("</td><td class=\"text-center position-relative\">");
		print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
		print("</td></tr>\n");
		if (isset($program->majors) && !empty($program->majors)) {
			print("		<tr><td class=\"small position-relative\">");
			print("<a class=\"stretched-link\" href=\"mapping.php?code=" . $program->code . "\">" . $program->code . "</a>");
			print("</td><td class=\"position-relative\">");
			print("<a class=\"stretched-link\" href=\"mapping.php?code=" . $program->code . "\"><span class=\"text-decoration-underline\">engineering core</span>: " . htmlentities($program->program, ENT_QUOTES|ENT_HTML5) . "</a>");
			print("</td><td class=\"text-center position-relative\">");
			print("<a class=\"stretched-link\" href=\"" . generateLinkToProgramsAndCourses($program->code) . "\">" . biBoxArrowUpRight() . "</a>");
			print("</td></tr>\n");
			foreach ($program->majors as $majorCode) {
				foreach ($majors as $major) {
					if ($major->code == $majorCode) {
						print("		<tr><td class=\"small position-relative\">");
						print("<a class=\"stretched-link\" href=\"mapping.php?code=" . $major->code . "\">" . $major->code . "</a>");
						print("</td><td class=\"position-relative\">");
						print("<a class=\"stretched-link\" href=\"mapping.php?code=" . $major->code . "\"><span class=\"text-decoration-underline\">major</span>: " . htmlentities($major->name, ENT_QUOTES|ENT_HTML5) . "</a>");
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

?>
</body>
</html>
