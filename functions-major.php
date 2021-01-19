<?php
/*
	functions-major.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-program.php');





// This function creates major details and generates $name and $shortname that can be displayed in HTML
function createMajorDetails($code, $codeProgram, &$major, &$program, &$name, &$shortname)
{
	$major = getDefinition($code);
	if ( $major && isset($major->name) )
	{
		$name = htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ' major';
		$shortname = $name;
		if ( $codeProgram )
		{
			$program = getProgram($codeProgram, 'full');
			if ( $program && $program->name )
			{
				$programDefinition = getDefinition($codeProgram);
				if ( $programDefinition && isset($programDefinition->name) )
				{
					$program->courses = $programDefinition->courses;
					$program->coursesForAggregating = array();
					if ( isset($programDefinition->coursesForAggregating) )
					{
						$program->coursesForAggregating = $programDefinition->coursesForAggregating;
					}
					else
					{
						$program->coursesForAggregating = $programDefinition->courses;
					}
					$program->majors = $programDefinition->majors;
					$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5). '  (major in ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
					$shortname = htmlspecialchars($programDefinition->program, ENT_QUOTES|ENT_HTML5). ' (' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
				}
			}
			else
			{
				$program = getDefinition($codeProgram);
				if ( $program && isset($program->name) )
				{
					if ( !isset($program->coursesForAggregating) )
					{
						$program->coursesForAggregating = $program->courses;
					}
					$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . '  (major in ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
					$shortname = htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . ' (' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
				}
			}
		}
		if ( !$name )
		{
			$name = htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ' major';
			$shortname = $name;
		}
	}
}





// This function prints HTML for a single major
function displayMajorPage($name, $shortname, $major, $program, $codeProgram, $courseCodes, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript)
{
	echo PHP_EOL . '<!-- begin major information -->' . PHP_EOL . PHP_EOL;
	echo '<main>' . PHP_EOL;
	echo '	<section>' . PHP_EOL;
	echo '		<h2>' . $name . '</h2>' . PHP_EOL;
	echo '		<div class="alert alert-info fst-italic" role="alert">Note: information provided here is indicative only. For full and current information about this major view the official page on P&amp;C.';
	if ( !isset($program) || !$program || !isset($program->name) )
	{
		echo '<br> This page depicts information about this major in a stand-alone fashion, i.e. not including the core degree it is a part of. To see the combined information of this major as part of a full degree, select one of the “<a class="alert-link" href="#parentPrograms">parent program(s)</a>” below.';
	}
	echo '</div>' . PHP_EOL;
	echo '		<table class="table">' . PHP_EOL;
	echo '			<tbody>' . PHP_EOL;
	if ( $program && isset($program->name) )
	{
		echo '				<tr><th>program: </th><td class="fst-italic">' . $shortname . '</td></tr>' . PHP_EOL;
	}
	else
	{
		echo '				<tr><th>major: </th><td class="fst-italic">' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ' major</td></tr>' . PHP_EOL;
	}
	if ($accreditationDisplayScript)
	{
		echo '				<tr><th><i>JCUMap</i> files: </th><td><ul class="m-0">';
		if ( file_exists('majors/' . $major->code . '.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'majors/' . $major->code . '.xml" download>' . $major->code . '.xml</a> ← <small>(download this file to revise the major mapping)</small></li>';
		}
		if ( file_exists('majors/' . $major->code . 'MappingResult.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'majors/' . $major->code . 'MappingResult.xml" download>' . $major->code . 'MappingResult.xml</a></li>';
		}
		if ( $program && isset($program->code) && file_exists('programs/' . $program->code . '.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'programs/' . $program->code . '.xml" download>' . $program->code . '.xml</a> ← <small>(download this file to revise the program mapping)</small></li>';
		}
		if ( $program && isset($program->code) && file_exists('programs/' . $program->code . 'MappingResult.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'programs/' . $program->code . 'MappingResult.xml" download>' . $program->code . 'MappingResult.xml</a></li>';
		}
		echo '</ul></td></tr>' . PHP_EOL;
	}
	
	if ( ( $program && isset($program->description) && $program->description ) || ( isset($major->description) && $major->description ) )
	{
		echo '				<tr><th>description: </th><td class="small">';
		if ( $program && isset($program->description) && $program->description )
		{
			echo str_replace("\n", '<br> ', str_replace("\r\n", '<br> ', trim($program->description))) . '<br><br> ';
		}
		echo htmlspecialchars(trim($major->description), ENT_QUOTES|ENT_HTML5);
		echo '</td></tr>' . PHP_EOL;
	}
	echo '				<tr><th>P&amp;C: </th><td>';
	if ( $program && isset($program->code) && $program->code )
	{
		echo '<a href="' . generateLinkToProgramsAndCourses($program->code) . '">' . generateLinkToProgramsAndCourses($program->code) . '</a><br> ';
	}
	echo '<a href="' . generateLinkToProgramsAndCourses($major->code) . '">' . generateLinkToProgramsAndCourses($major->code) . '</a>';
	echo '</td></tr>' . PHP_EOL;
	if ( !( $program && isset($program->name) ) && isset($major->programs) && $major->programs )
	{
		echo '				<tr id="parentPrograms"><th>parent program(s): </th><td class="small" style="column-count: 2;"><ul>' . PHP_EOL;
		foreach ($major->programs as $program)
		{
			$program = getDefinition($program);
			if ( $program )
			{
				echo '					<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code), array('major', $major->code)) . '">' . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . '</a></li>' . PHP_EOL;
			}
		}
		echo '				</ul></td></tr>' . PHP_EOL;
	}
	if ( ( isset($program->courses) && $program->courses ) || ( isset($major->courses) && $major->courses ) )
	{
		if ( $codeProgram && isset($program->courses) && $program->courses )
		{
			echo '				<tr><th>courses in core: </th><td class="small" style="column-count: 2;"><ul>' . PHP_EOL;
			foreach ($program->courses as $courseKey => $course)
			{
				$course = getCourse($course, $courseCodes, 'full');
				if ( $course )
				{
					echo '					<li>';
					if ( $course->name )
					{
						echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('course', $course->code)) . '">' . $course->code . ' — <span class="fst-italic">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</span></a>';
					}
					else
					{
						echo '<a href="' . generateLinkToProgramsAndCourses($course->code) . '">' . $course->code . '</a>';
					}
					if ( $accreditationDisplayScript && isset($program->coursesForAggregating) && in_array($course->code, $program->coursesForAggregating) )
					{
						echo '<sup class="text-danger">*</sup>';
					}
					echo '</li>' . PHP_EOL;
				}
				unset($program->courses[$courseKey]);
				$program->courses[$course->code] = $course;
			}
			echo '				</ul>';
			if ( $accreditationDisplayScript )
			{
				echo '<span class="small"><sup class="text-danger">*</sup> These courses are used to create the aggregate summaries below.</span>';
			}
			echo '</td></tr>' . PHP_EOL;
		}
		echo '				<tr><th>courses in major: </th><td class="small" style="column-count: 2;"><ul>' . PHP_EOL;
		foreach ($major->courses as $courseKey => $course)
		{
			$course = getCourse($course, $courseCodes, 'full');
			if ( $course )
			{
				echo '					<li>';
				if ( $course->name )
				{
					echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('course', $course->code)) . '">' . $course->code . ' — <span class="fst-italic">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</span></a>';
				}
				else
				{
					echo '<a href="' . generateLinkToProgramsAndCourses($course->code) . '">' . $course->code . '</a>';
				}
				if ( $accreditationDisplayScript )
				{
					echo '<sup class="text-danger">*</sup>';
				}
				echo '</li>' . PHP_EOL;
			}
			unset($major->courses[$courseKey]);
			$major->courses[$course->code] = $course;
		}
		echo '				</ul></td></tr>' . PHP_EOL;
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
				echo '				<tr><th>assessment: </th><td class="small">';
				echo '<table class="table table-sm table-bordered table-hover caption-top"><caption class="fst-italic">assessment types used across whole ';
				if ( $program && isset($program->name) )
				{
					echo 'degree';
				}
				else
				{
					echo 'major';
				}
				echo '</caption>';
				echo '<thead class="bg-light"><tr><th>assessment type</th><th colspan="2" class="text-center">contribution to overall assessment</th></tr></thead><tbody>';
				foreach ($assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
				{
					if ( $assessmentTypeCredits > 0.0 )
					{
						$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
						if ( $assessmentTypePercentage == 0 )
						{
							$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 1);
						}
						echo '<tr><td class="align-middle">' . $assessmentTypes[$assessmentType]->type . '</td><td class="text-center align-middle">' . $assessmentTypePercentage . '%</td><td class="col-6 align-middle"><div class="progress bg-transparent"><div class="progress-bar" role="progressbar" style="width: ' . $assessmentTypePercentage . '%" aria-valuenow="' . $assessmentTypePercentage . '" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip" data-bs-placement="right" title="' . $assessmentTypePercentage . '%"></div></div></td></tr>';
					}
				}
				echo '</tbody></table>';
				echo '</td></tr>' . PHP_EOL;
			}
		}
	}
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	
	// display chart of development level learning against each of the competencies
	$majorCompetencies = array();
	$majorCompetencyName = '';
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
		if ( $program->code == 'AENGI' || $program->code == 'AENRD') {
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
		echo '		<section>' . PHP_EOL;
		echo '			<h3>Cumulative annual progress towards the ' . $majorCompetencyName . '</h3>' . PHP_EOL;
		if ( $accreditationDisplayScript )
		{
			echo '			<p>This table maps how the unit credits across this ';
			if ( $program && isset($program->name) )
			{
				echo 'degree';
			}
			else
			{
				echo 'major';
			}
			echo ' cumulatively contribute towards achievement of the ' . $majorCompetencyName . '.</p>' . PHP_EOL;
		}
		else
		{
			echo '			<p>This table depicts the relative cumulative contribution of this ';
			if ( $program && isset($program->name) )
			{
				echo 'degree';
			}
			else
			{
				echo 'major';
			}
			echo ' towards the ' . $majorCompetencyName . '. <em>Note that this illustration is indicative only, and does not include contributions from additional courses you may undertake</em>.</p>' . PHP_EOL;
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
		$colSpan = 0;
		echo '			<table class="table table-sm table-hover small">' . PHP_EOL;
		if ( $accreditationDisplayScript )
		{
			$colSpan = 1;
			echo '				<thead class="bg-light sticky-top"><tr><th class="col-1"></th><th class="text-start border-start">0.0</th><th class="text-end border-end">' . $maxUnits . '.0</th></tr></thead>' . PHP_EOL;
		}
		echo '				<tbody>' . PHP_EOL;
		foreach ($majorCompetencies as $competencyKey => $competency)
		{
			if ( $competency->level == 1 )
			{
				echo '					<tr class="table-secondary"><td colspan="' . ( 2 + $colSpan ) . '" class="fw-bold">' . $competency->label . ' ' . $competency->text . '</td></tr>' . PHP_EOL;
			}
			else if ( $competency->level == 2 )
			{
				echo '					<tr class="border-bottom"><td class="text-center align-middle border-end col-1" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</td><td colspan="' . ( 1 + $colSpan ) . '" class="align-middle">' . PHP_EOL;
				foreach ($majorMappingData[$competency->label] as $year => $DLs)
				{
					echo '						<div class="progress bg-transparent">';
					if ( $accreditationDisplayScript )
					{
						if ( isset($majorMappingData[$competency->label][$year][1]) && $majorMappingData[$competency->label][$year][1] > 0.0)
						{
							$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][1] / $maxUnits;
							echo '<div class="progress-bar bg-success text-start border" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $majorMappingData[$competency->label][$year][1] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($majorMappingData[$competency->label][$year][1], 2) . '">';
							if ( $year > 3 )
							{
								echo 'DL1';
							}
							echo '</div>';
						}
						if ( isset($majorMappingData[$competency->label][$year][2]) && $majorMappingData[$competency->label][$year][2] > 0.0)
						{
							$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][2] / $maxUnits;
							echo '<div class="progress-bar bg-primary text-center border" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $majorMappingData[$competency->label][$year][2] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($majorMappingData[$competency->label][$year][2], 2) . '">';
							if ( $year > 3 )
							{
								echo 'DL2';
							}
							echo '</div>';
						}
						if ( isset($majorMappingData[$competency->label][$year][3]) && $majorMappingData[$competency->label][$year][3] > 0.0)
						{
							$mappingPercentage = 100 * $majorMappingData[$competency->label][$year][3] / $maxUnits;
							echo '<div class="progress-bar bg-danger text-end border" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $majorMappingData[$competency->label][$year][3] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($majorMappingData[$competency->label][$year][3], 2) . '">';
							if ( $year > 3 )
							{
								echo 'DL3';
							}
							echo '</div>';
						}
					}
					else
					{
						$sumOfUnits = array_sum($majorMappingData[$competency->label][$year]);
						if ( $sumOfUnits > 0.0)
						{
							$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
							echo '<div class="progress-bar border" role="progressbar" style="width: ' . $mappingPercentage . '%"></div>';
						}
					}
					echo '&nbsp;yr&nbsp;' . $year . '</div>' . PHP_EOL;
				}
				if ( $accreditationDisplayScript && $bigProgramMappingData )
				{
					echo '						<div class="progress bg-transparent">';
					$sumOfUnits = array_sum($bigProgramMappingData[$competency->label]);
					$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
					echo '<div class="progress-bar bg-dark text-center border" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $sumOfUnits . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($sumOfUnits, 2) . '">';
					echo 'program</div>';
					echo '</div>' . PHP_EOL;
				}
				echo '					</td></tr>' . PHP_EOL;
			}
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
		echo '		</section>' . PHP_EOL;
	}
	
	// list all EA competencies, and indicate which are addressed in this major
	if ( $majorCompetencies )
	{
		echo '		<section>' . PHP_EOL;
		echo '			<h3>' . $majorCompetencyName . ' — summary</h3>' . PHP_EOL;
		if ( $accreditationDisplayScript && isset($bigProgramCompetencies) && $bigProgramCompetencies )
		{
			echo '			<div class="alert alert-info fst-italic" role="alert">Note: the black ticks (<span class="text-dark">✓</span>) represent the expectation of attainment of each competency as depicted via the program learning outcomes, and the coloured ticks (<span class="text-success">✓</span>, <span class="text-primary">✓</span>, <span class="text-danger">✓</span>) represent attainment of each competency via aggregating from the courses.</div>' . PHP_EOL;
		}
		echo '			<table class="table table-sm table-hover">' . PHP_EOL;
		echo '				<tbody>' . PHP_EOL;
		$colSpan = 0;
		if ( $accreditationDisplayScript )
		{
			$colSpan = 1;
		}
		foreach ($majorCompetencies as $competencyKey => $competency)
		{
			switch ($competency->level)
			{
				case 1:
					echo '					<tr class="table-secondary">';
					$oneSpan = 3 + $colSpan;
					if ( $accreditationDisplayScript && $bigProgramCompetencies)
					{
						$oneSpan++;
					}
					echo '<th colspan="' . $oneSpan . '">' . $competency->label . ' ' . $competency->text . '</th>';
					echo '</tr>' . PHP_EOL;
					break;
				case 2:
					echo '					<tr class="small">';
					if ( $accreditationDisplayScript && $bigProgramCompetencies)
					{
						if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
						{
							echo '<td class="text-center text-dark align-top">✓</td>';
						}
						else
						{
							echo '<td></td>';
						}
					}
					if ( $competency->competencyLevel > 0 )
					{
						echo '<td class="text-center ';
						if ( $accreditationDisplayScript )
						{
							switch ($competency->competencyLevel)
							{
								case 1:
									echo 'text-success ';
									break;
								case 2:
									echo 'text-primary ';
									break;
								case 3:
									echo 'text-danger ';
									break;
							}
						}
						else
						{
							echo 'text-success ';
						}
						echo 'align-top">✓</td>';
					}
					else
					{
						echo '<td></td>';
					}
					echo '<td>' . $competency->label . '</td><td colspan="' . ( 1 + $colSpan ) . '">' . $competency->text . '</td>';
					echo '</tr>' . PHP_EOL;
					break;
				case 3:
					if ( $accreditationDisplayScript )
					{
						echo '					<tr class="small">';
						if ( $bigProgramCompetencies)
						{
							if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
							{
								echo '<td class="small text-center text-dark align-top">✓</td>';
							}
							else
							{
								echo '<td></td>';
							}
						}
						if ( $colSpan )
						{
							echo '<td class="small" colspan="' . $colSpan . '"></td>';
						}
						if ( $competency->competencyLevel > 0 )
						{
							echo '<td class="small text-center ';
							switch ($competency->competencyLevel)
							{
								case 1:
									echo 'text-success ';
									break;
								case 2:
									echo 'text-primary ';
									break;
								case 3:
									echo 'text-danger ';
									break;
							}
							echo 'align-top">✓</td>';
						}
						else
						{
							echo '<td class="small"></td>';
						}
						echo '<td class="small">' . $competency->label . '</td><td class="small">' . $competency->text . '</td>';
						echo '</tr>' . PHP_EOL;
					}
					break;
			}
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
		echo '		</section>' . PHP_EOL;
	}
	echo '	</section>' . PHP_EOL;
	echo '</main>' . PHP_EOL;
	echo PHP_EOL . '<!-- end major information -->' . PHP_EOL . PHP_EOL;
}
