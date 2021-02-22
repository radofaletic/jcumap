<?php
/*
	functions-program.php

	by © 2020–2021 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-course.php');




// This function produces an array of all program codes available on this site.
// 	The format of the array is [program_code] => [program_code]
function listAllPrograms()
{
	$programs = array();
	$directory = opendir('./programs');
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 7 <= strlen($filename) && ctype_upper( substr($filename, 0, -4) ) && substr($filename, -4) == '.xml' && substr($filename, -17) != 'MappingResult.xml' )
		{
			$program = substr($filename, 0, -4);
			$programs[$program] = $program;
		}
    }
    ksort($programs);
    return $programs;
}





// This function creates major details and generates $name and $shortname that can be displayed in HTML
function createProgramDetails($code, &$program, &$name, &$shortname)
{
	$program = getProgram($code, 'full');
	if ( $program && $program->name )
	{
		$programDefinition = getDefinition($code);
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
			$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5);
			$shortname = htmlspecialchars($programDefinition->program, ENT_QUOTES|ENT_HTML5);
			if ( isset($program->majors) && $program->majors )
			{
				$name .= ' core';
				$shortname .= ' (core)';
			}
		}
	}
	else
	{
		$program = getDefinition($code);
		if ( $program && isset($program->name) )
		{
			if ( !isset($program->coursesForAggregating) )
			{
				$program->coursesForAggregating = $program->courses;
			}
			$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5);
			$shortname = htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5);
			if ( isset($program->majors) && $program->majors )
			{
				$name .= ' core';
				$shortname .= ' (core)';
			}
		}
	}
}





// This function is a specialised alias of getCourse(...)
function getProgram($code, $details = 'basic')
{
	return getCourse($code, array(), $details, 'program');
}





// This function prints HTML for a program
function displayProgramPage($name, $program, $courseCodes, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript)
{
	$masters = false;
	if ( substr($name, 0, 6) == 'Master' )
	{
		$masters = true;
	}
	echo PHP_EOL . '<!-- begin program information -->' . PHP_EOL . PHP_EOL;
	echo '<section class="margintop padtop">' . PHP_EOL;
	echo '	<h2>' . $name . '</h2>' . PHP_EOL;
	echo '	<p class="msg-info" role="alert">Note: information provided here is indicative only. For full and current information about this program view the official page on P&amp;C.</p>' . PHP_EOL;
	echo '	<table class="fullwidth tbl-row-bdr anu-long-area">' . PHP_EOL;
	echo '		<tbody>' . PHP_EOL;
	echo '			<tr><th>program: </th><td>' . htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . '</td></tr>' . PHP_EOL;
	if ($accreditationDisplayScript)
	{
		echo '			<tr><th><i>JCUMap</i> files: </th><td><ul class="noindent">';
		if ( file_exists('programs/' . $program->code . '.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'programs/' . $program->code . '.xml" download>' . $program->code . '.xml</a> ← <small>(download this file to revise the program mapping)</small></li>';
		}
		if ( file_exists('programs/' . $program->code . 'MappingResult.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'programs/' . $program->code . 'MappingResult.xml" download>' . $program->code . 'MappingResult.xml</a></li>';
		}
		echo '</ul></td></tr>' . PHP_EOL;
	}
	if ( isset($program->description) && $program->description )
	{
		echo '			<tr><th>description: </th><td class="small">' . str_replace("\n", '<br> ', str_replace("\r\n", '<br> ', trim($program->description))) . '</td></tr>' . PHP_EOL;
	}
	echo '			<tr><th>P&amp;C: </th><td><a href="' . generateLinkToProgramsAndCourses($program->code) . '">' . generateLinkToProgramsAndCourses($program->code) . '</a></td></tr>' . PHP_EOL;
	if ( isset($program->learningOutcomes) && $program->learningOutcomes )
	{
		echo '			<tr id="programLearningOutcomes"><th>program learning outcomes: </th><td class="small"><ol class="noindent">';
		foreach ($program->learningOutcomes as $learningOutcome)
		{
			if ( $learningOutcome )
			{
				echo '<li>' . $learningOutcome . '</li>';
			}
		}
		echo '</ol></td></tr>' . PHP_EOL;
	}
	if ( isset($program->majors) && $program->majors )
	{
		echo '			<tr><th>majors: </th><td class="small" style="column-count: 2;"><ul class="noindent">' . PHP_EOL;
		foreach ($program->majors as $major)
		{
			$major = getDefinition($major);
			if ( $major )
			{
				echo '				<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code), array('major', $major->code)) . '">' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . '</a></li>' . PHP_EOL;
			}
		}
		echo '			</ul></td></tr>' . PHP_EOL;
	}
	if ( isset($program->courses) && $program->courses )
	{
		echo '			<tr><th>courses in ';
		if ( isset($program->majors) && $program->majors )
		{
			echo 'core';
		}
		else
		{
			echo 'program';
		}
		echo ': </th><td class="small" style="column-count: 2;"><ul class="noindent">' . PHP_EOL;
		foreach ($program->courses as $courseKey => $course)
		{
			$course = getCourse($course, $courseCodes, 'full');
			echo '					<li>';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('course', $course->code)) . '">' . $course->code . ' — <span>' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</span></a>';
			}
			else
			{
				echo '<a href="' . generateLinkToProgramsAndCourses($course->code) . '">' . $course->code . '</a>';
			}
			if ( $accreditationDisplayScript && ( !isset($program->coursesForAggregating) || in_array($course->code, $program->coursesForAggregating) ) )
			{
				echo '<sup class="text-college">*</sup>';
			}
			echo '</li>' . PHP_EOL;
			$program->courses[$courseKey] = $course;
		}
		echo '				</ul>';
		if ( $accreditationDisplayScript )
		{
			echo '<span class="small"><sup class="text-college">*</sup> These courses are used to create the aggregate summaries below.</span>';
		}
		echo '</td></tr>' . PHP_EOL;
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
			echo '			<tr><th>assessment: </th><td class="small">';
			echo '<table class="fullwidth tbl-cell-bdr"><caption>assessment types used across whole ';
			if ( isset($program->majors) && $program->majors )
			{
				echo 'core';
			}
			else
			{
				echo 'program';
			}
			echo '</caption>';
			echo '<thead><tr><th>assessment type</th><th colspan="2" class="text-center">contribution to overall assessment</th></tr></thead><tbody>';
			foreach ($assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
			{
				if ( $assessmentTypeCredits > 0.0 )
				{
					$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 0);
					if ( $assessmentTypePercentage == 0 )
					{
						$assessmentTypePercentage = number_format((100 * $assessmentTypeCredits / $assessmentTotals), 1);
					}
					echo '<tr><td>' . $assessmentTypes[$assessmentType]->type . '</td><td class="text-center">' . $assessmentTypePercentage . '%</td><td class="w-doublenarrow"><div class="fullwidth"><div class="bg-grey50" role="progressbar" style="width: ' . $assessmentTypePercentage . '%" aria-valuenow="' . $assessmentTypePercentage . '" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip" data-bs-placement="right" title="' . $assessmentTypePercentage . '%">&nbsp;</div></div></td></tr>';
				}
			}
			echo '</tbody></table>';
			echo '</td></tr>' . PHP_EOL;
		}
	}
	echo '		</tbody>' . PHP_EOL;
	echo '	</table>' . PHP_EOL;
	
	// display chart of development level learning against each of the competencies
	$programCompetencies = array();
	$programCompetencyName = '';
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
			if ( $masters )
			{
				$courseYear = 1;
			}
			foreach ($course->mappingData as $competencyLabel => $DLs)
			{
				if ( !isset($programMappingData[$competencyLabel]) )
				{
					if ( $masters )
					{
						$programMappingData[$competencyLabel] = array(1 => array(1 => 0.0, 2 => 0.0, 3 => 0.0));
					}
					else
					{
						$programMappingData[$competencyLabel] = array(1 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 2 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 3 => array(1 => 0.0, 2 => 0.0, 3 => 0.0), 4 => array(1 => 0.0, 2 => 0.0, 3 => 0.0));
					}
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
		echo '	<section class="margintop padtop">' . PHP_EOL;
		echo '		<h3>Program ';
		if ( isset($program->majors) && $program->majors )
		{
			echo 'core ';
		}
		echo 'cumulative contribution towards the ' . $programCompetencyName . '</h3>' . PHP_EOL;
		if ( $accreditationDisplayScript )
		{
			echo '		<p>This table maps how the unit credits across this program ';
			if ( isset($program->majors) && $program->majors )
			{
				echo 'core ';
			}
			echo 'cumulatively contribute towards achievement of the ' . $programCompetencyName . '.</p>' . PHP_EOL;
		}
		else
		{
			echo '		<p>This table depicts the relative cumulative contribution of this program ';
			if ( isset($program->majors) && $program->majors )
			{
				echo 'core ';
			}
			echo 'towards the ' . $programCompetencyName . '. <em>Note that this illustration is indicative only, and does not take into account the contributions from any additional courses you may take (which includes majors, minors and specialisations)</em>.</p>' . PHP_EOL;
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
		
		echo '		<table class="fullwidth tbl-cell-bdr noborder anu-long-area small">' . PHP_EOL;
		$colSpan = 0;
		if ( $accreditationDisplayScript )
		{
			$colSpan = 1;
			echo '			<thead class="anu-sticky-header">' . PHP_EOL;
			echo '				<tr class="small"><th class="msg-info" colspan="3">Colour legend for learning development levels: <span class="bg-grey25 padleft padright">DL1</span> <span class="bg-uni75 padleft padright" data-bs-toggle="tooltip">DL2</span> <span class="bg-college50 padleft padright">DL3</span></th></tr>' . PHP_EOL;
			echo '				<tr><th></th><th class="text-left bdr-left-solid"><img style="width: 1em; height: 1em;" src="//style.anu.edu.au/_anu/images/icons/web/first.png" alt="0.0" /> 0.0</th><th class="text-right bdr-right-solid">' . $maxUnits . '.0 <img style="width: 1em; height: 1em;" src="//style.anu.edu.au/_anu/images/icons/web/last.png" alt="' . $maxUnits . '.0" /></th></tr>' . PHP_EOL;
			echo '			</thead>' . PHP_EOL;
		}
		echo '			<tbody>' . PHP_EOL;
		foreach ($programCompetencies as $competencyKey => $competency)
		{
			if ( $competency->level == 1 )
			{
				echo '				<tr class="table-secondary"><td colspan="' . ( 2 + $colSpan ) . '"><strong>' . $competency->label . ' ' . $competency->text . '</strong></td></tr>' . PHP_EOL;
			}
			else if ( $competency->level == 2 )
			{
				echo '				<tr><td style="width: 10%;" class="text-center bdr-right-solid" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</td><td colspan="' . ( 1 + $colSpan ) . '" class="align-middle">' . PHP_EOL;
				foreach ($programMappingData[$competency->label] as $year => $DLs)
				{
					echo '					<div class="fullwdith" style="margin-bottom: 2px; white-space: nowrap;">';
					if ( $accreditationDisplayScript )
					{
						if ( isset($programMappingData[$competency->label][$year][1]) && $programMappingData[$competency->label][$year][1] > 0.0)
						{
							$mappingPercentage = 100 * $programMappingData[$competency->label][$year][1] / $maxUnits;
							echo '<div class="bg-grey25 text-left" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%" aria-valuenow="' . $programMappingData[$competency->label][$year][1] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($programMappingData[$competency->label][$year][1], 2) . '">&nbsp;</div>';
						}
						if ( isset($programMappingData[$competency->label][$year][2]) && $programMappingData[$competency->label][$year][2] > 0.0)
						{
							$mappingPercentage = 100 * $programMappingData[$competency->label][$year][2] / $maxUnits;
							echo '<div class="bg-uni75 text-center" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%" aria-valuenow="' . $programMappingData[$competency->label][$year][2] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($programMappingData[$competency->label][$year][2], 2) . '">&nbsp;</div>';
						}
						if ( isset($programMappingData[$competency->label][$year][3]) && $programMappingData[$competency->label][$year][3] > 0.0)
						{
							$mappingPercentage = 100 * $programMappingData[$competency->label][$year][3] / $maxUnits;
							echo '<div class="bg-college50 text-right" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%" aria-valuenow="' . $programMappingData[$competency->label][$year][3] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($programMappingData[$competency->label][$year][3], 2) . '">&nbsp;</div>';
						}
					}
					else
					{
						$sumOfUnits = array_sum($programMappingData[$competency->label][$year]);
						if ( $sumOfUnits > 0.0)
						{
							$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
							echo '<div class="bg-college50" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%">&nbsp;</div>';
						}
					}
					if ( !$masters )
					{
						echo '&nbsp;yr&nbsp;' . $year;
					}
					echo '</div>' . PHP_EOL;
				}
				if ( $accreditationDisplayScript && $bigProgramMappingData )
				{
					echo '					<div class="fullwdith">';
					$sumOfUnits = array_sum($bigProgramMappingData[$competency->label]);
					$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
					echo '<div class="bg-grey50 text-center" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%" aria-valuenow="' . $sumOfUnits . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($sumOfUnits, 2) . '">';
					echo 'program</div>';
					echo '</div>' . PHP_EOL;
				}
				echo '				</td></tr>' . PHP_EOL;
			}
		}
		echo '			</tbody>' . PHP_EOL;
		echo '		</table>' . PHP_EOL;
		echo '	</section>' . PHP_EOL;
	}
	
	// list all EA competencies, and indicate which are addressed in this program (core)
	if ( $programCompetencies )
	{
		echo '	<section class="margintop padtop">' . PHP_EOL;
		echo '		<h3>' . $programCompetencyName . ' — summary</h3>' . PHP_EOL;
		echo '		<table class="fullwidth tbl-row-bdr noborder anu-long-area">' . PHP_EOL;
		$colSpan = 0;
		if ( $accreditationDisplayScript )
		{
			$colSpan = 1;
			$oneSpan = 3 + $colSpan;
			echo '			<thead class="anu-sticky-header small">' . PHP_EOL;
			if ( isset($bigProgramCompetencies) && $bigProgramCompetencies )
			{
				$oneSpan++;
				echo '				<tr class="small"><th colspan="' . $oneSpan . '" class="msg-info" role="alert">Note: the grey ticks (<span class="text-grey">✓</span>) represent the expectation of attainment of each competency as depicted via the <a href="#programLearningOutcomes">program learning outcomes</a>, and the coloured backgrounds (<span class="bdr-top-solid bdr-bottom-solid bdr-college bg-grey25 padleft padright">DL1</span>, <span class="bdr-top-solid bdr-bottom-solid bdr-college bg-uni75 padleft padright">DL2</span>, <span class="bdr-top-solid bdr-bottom-solid bdr-college bg-college50 padleft padright">DL3</span>) represent attainment of each competency via aggregating from the courses.</th></tr>' . PHP_EOL;
			}
			else
			{
				echo '				<tr class="small"><th colspan="' . $oneSpan . '" class="msg-info" role="alert">The coloured backgrounds (<span class="bdr-top-solid bdr-bottom-solid bdr-college bg-grey25 padleft padright">DL1</span>, <span class="bdr-top-solid bdr-bottom-solid bdr-college bg-uni75 padleft padright">DL2</span>, <span class="bdr-top-solid bdr-bottom-solid bdr-college bg-college50 padleft padright">DL3</span>) represent attainment of each competency via aggregating from the courses.</th></tr>' . PHP_EOL;
			}
			echo '			</thead>' . PHP_EOL;
		}
		echo '			<tbody>' . PHP_EOL;
		foreach ($programCompetencies as $competencyKey => $competency)
		{
			switch ($competency->level)
			{
				case 1:
					echo '				<tr>';
					$oneSpan = 3 + $colSpan;
					if ( $accreditationDisplayScript && $bigProgramCompetencies)
					{
						$oneSpan++;
					}
					echo '<th colspan="' . $oneSpan . '">' . $competency->label . ' ' . $competency->text . '</th>';
					echo '</tr>' . PHP_EOL;
					break;
				case 2:
					echo '				<tr';
					if ( $competency->competencyLevel > 0 )
					{
						if ( $accreditationDisplayScript )
						{
							switch ($competency->competencyLevel)
							{
								case 1:
									echo ' class="small bg-grey25"';
									break;
								case 2:
									echo ' class="small bg-uni75"';
									break;
								case 3:
									echo ' class="small bg-college50"';
									break;
							}
						}
						else
						{
							echo ' class="small"';
						}
					}
					else
					{
						echo ' class="small"';
					}
					echo '>';
					if ( $accreditationDisplayScript && $bigProgramCompetencies)
					{
						if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
						{
							echo '<td class="text-center text-grey">✓</td>';
						}
						else
						{
							echo '<td></td>';
						}
					}
					if ( $competency->competencyLevel > 0 && !$accreditationDisplayScript )
					{
						echo '<td class="text-center">✓</td>';
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
						echo '				<tr';
						if ( $competency->competencyLevel > 0 )
						{
							switch ($competency->competencyLevel)
							{
								case 1:
									echo ' class="small bg-grey25"';
									break;
								case 2:
									echo ' class="small bg-uni75"';
									break;
								case 3:
									echo ' class="small bg-college50"';
									break;
							}
						}
						else
						{
							echo ' class="small"';
						}
						echo '>';
						if ( $bigProgramCompetencies)
						{
							if ( $bigProgramCompetencies[$competencyKey]->competencyLevel > 0 )
							{
								echo '<td class="small text-center text-grey">✓</td>';
							}
							else
							{
								echo '<td class="small"></td>';
							}
						}
						if ( $colSpan )
						{
							echo '<td class="small" colspan="' . $colSpan . '"></td>';
						}
						echo '<td class="small text-center"></td>';
						echo '<td class="small">' . $competency->label . '</td><td class="small">' . $competency->text . '</td>';
						echo '</tr>' . PHP_EOL;
					}
					break;
			}
		}
		echo '			</tbody>' . PHP_EOL;
		echo '		</table>' . PHP_EOL;
		echo '	</section>' . PHP_EOL;
	}
	echo '</section>' . PHP_EOL;
	echo PHP_EOL . '<!-- end program information -->' . PHP_EOL . PHP_EOL;
}
