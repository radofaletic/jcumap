<?php
/*
	functions-course.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-html.php');





// This function produces an array of all course codes available on this site.
// 	The format of the array is [course_code] => [course_file_basename]
function listAllCourseCodes()
{
	$courses = array();
	$directory = opendir('./courses');
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 12 <= strlen($filename) && ctype_upper( substr($filename, 0, 4) ) && ctype_digit( substr($filename, 4, 4) ) && substr($filename, -4) == '.xml' && substr($filename, -17) != 'MappingResult.xml' )
		{
			$courseFilename = substr($filename, 0, -4);
			$coursePrefix = substr($filename, 0, 4);
			$courseNumber = substr($filename, 4, 4);
			$courses[$coursePrefix . $courseNumber] = $courseFilename;
			$leftover = substr($filename, 8, -4);
			if ( strlen($leftover) )
			{
				if ( substr($leftover, 0, 1) == '-' )
				{
					$leftover = substr($leftover, 1);
				}
				if ( 8 <= strlen($leftover) && ctype_upper( substr($leftover, 0, 4) ) && ctype_digit( substr($leftover, 4, 4) ) )
				{
					$coursePrefix = substr($leftover, 0, 4);
					$courseNumber = substr($leftover, 4, 4);
					$courses[$coursePrefix . $courseNumber] = $courseFilename;
				}
				else if ( 4 <= strlen($leftover) && ctype_digit( substr($leftover, 0, 4) ) )
				{
					$courseNumber = substr($leftover, 0, 4);
					$courses[$coursePrefix . $courseNumber] = $courseFilename;
				}
			}
		}
    }
    ksort($courses);
    return $courses;
}





// This function creates course details and generates $name and $shortname that can be displayed in HTML
function createCourseDetails($code, $courseCodes, &$course, &$name, &$shortname)
{
	$course = getCourse($code, $courseCodes, 'full');
	if ( $course && $course->name )
	{
		$name = $code . ' — <span class="fst-italic">' . $course->name . '</span>';
		$shortname = $code;
	}
}





// This function reads the XML output files from JCUMap and prepares the data for use on this website
function getCourse($code, $courseCodes = array(), $detail = 'basic', $type = 'course')
{
	$data = new stdClass();
	$data->code = $code;
	$data->name = '';
	$data->fileName = $code;
	$data->description = '';
	$data->units = 0;
	$data->competencyName = '';
	$data->competencies = array();
	$data->learningOutcomes = array();
	$data->learningOutcomesMapping = array();
	$data->assessments = array();
	$data->assessmentsMapping = array();
	$data->assessmentCategorisationSummary = array();
	$data->mappingData = array();
	
	$dataDirectory = 'courses';
	if ( $type == 'program' || $type == 'fdd' )
	{
		$dataDirectory = 'programs';
	}
	
	if ( $type == 'course' )
	{
		if ( !$courseCodes )
		{
			$courseCodes = listAllCourseCodes();
		}
		if ( isset($courseCodes[$code]) )
		{
			$data->fileName = $courseCodes[$code];
		}
	}
	
	$dataFile = './' . $dataDirectory . '/' . $data->fileName . '.xml';
	
	if ( file_exists($dataFile) )
	{
		$rawXmlData = simplexml_load_file($dataFile);
		if ( $rawXmlData )
		{
			if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) )
			{
				$data->name = trim($rawXmlData->SubjectInfo->SubjectName);
				$data->description = trim($rawXmlData->SubjectInfo->SubjectDescription);
				if ( $type != 'fdd' )
				{
					$data->units = trim($rawXmlData->SubjectInfo->CreditPoints);
				}
				if ( $detail != 'basic' )
				{
					$data->data = $rawXmlData;
					if ( isset($rawXmlData->SLOCount) && $rawXmlData->SLOCount > 0 && isset($rawXmlData->SLOsAndMapping) )
					{
						$learningOutcomeNumber = 0;
						foreach ($rawXmlData->SLOsAndMapping->children() as $mapping)
						{
							$mapping->SubjectLearningOutcome = trim($mapping->SubjectLearningOutcome);
							if ( $mapping->SubjectLearningOutcome )
							{
								$data->learningOutcomes[$learningOutcomeNumber] = htmlspecialchars($mapping->SubjectLearningOutcome, ENT_QUOTES|ENT_HTML5);
								$data->learningOutcomesMapping[$learningOutcomeNumber] = array();
								if ( isset($mapping->PrimaryMapping) && isset($mapping->PrimaryMapping->ChildCompetencies) && $mapping->PrimaryMapping->ChildCompetencies )
								{
									$data->competencyName = htmlspecialchars($mapping->PrimaryMapping->DescriptionText, ENT_QUOTES|ENT_HTML5);
									foreach ($mapping->PrimaryMapping->ChildCompetencies as $competencyLevel1)
									{
										$key1 = '' . trim($competencyLevel1->Prefix);
										$competency1 = new stdClass();
										$competency1->level = 1;
										$competency1->sublevels = count($competencyLevel1->ChildCompetencies);
										$competency1->label = trim($competencyLevel1->Prefix);
										$competency1->text = str_replace(array('[b]', '[/b]', '[i]', '[/i]', "\\r\\n", "\\n"), array('<b>', '</b>', '<i>', '</i>', '<br>', '<br>'), htmlspecialchars(trim($competencyLevel1->DescriptionText), ENT_QUOTES|ENT_HTML5));
										$competency1->competencyLevel = 0;
										if ( isset($competencyLevel1->IsTicked) && $competencyLevel1->IsTicked == 'true' )
										{
											$data->learningOutcomesMapping[$learningOutcomeNumber][$key1] = 1;
											if ( isset($competencyLevel1->DL) )
											{
												$data->learningOutcomesMapping[$learningOutcomeNumber][$key1] = 0 + $competencyLevel1->DL;
											}
										}
										$data->competencies[$key1] = $competency1;
										if ( isset($competencyLevel1->ChildCompetencies) && $competencyLevel1->ChildCompetencies )
										{
											$mapping2 = false;
											$DL2 = 0;
											foreach ($competencyLevel1->ChildCompetencies as $competencyLevel2)
											{
												$key2 = '' . trim($competencyLevel2->Prefix);
												if ( substr($key2, 0, strlen($key1)) != $key1 )
												{
													if ( substr($key1, 0, -1) != '.' )
													{
														$key2 = '.' . $key2;
													}
													$key2 = $key1 . $key2;
												}
												$competency2 = new stdClass();
												$competency2->level = 2;
												$competency2->sublevels = count($competencyLevel2->ChildCompetencies);
												$competency2->label = trim($competencyLevel2->Prefix);
												$competency2->text = str_replace(array('[b]', '[/b]', '[i]', '[/i]', "\\r\\n", "\\n"), array('<b>', '</b>', '<i>', '</i>', '<br>', '<br>'), htmlspecialchars(trim($competencyLevel2->DescriptionText), ENT_QUOTES|ENT_HTML5));
												$competency2->competencyLevel = 0;
												if ( isset($competencyLevel2->IsTicked) && $competencyLevel2->IsTicked == 'true' )
												{
													$data->learningOutcomesMapping[$learningOutcomeNumber][$key2] = 1;
													if ( isset($competencyLevel2->DL) )
													{
														$data->learningOutcomesMapping[$learningOutcomeNumber][$key2] = 0 + $competencyLevel2->DL;
														if ( $data->learningOutcomesMapping[$learningOutcomeNumber][$key2] > $DL2 )
														{
															$DL2 = $data->learningOutcomesMapping[$learningOutcomeNumber][$key2];
														}
													}
													$mapping2 = true;
												}
												$data->competencies[$key2] = $competency2;
												$mapping3 = false;
												$DL3 = 0;
												foreach ($competencyLevel2->ChildCompetencies as $competencyLevel3)
												{
													$key3 = '' . trim($competencyLevel3->Prefix);
													if ( substr($key3, 0, strlen($key2)) != $key2 )
													{
														if ( substr($key2, 0, -1) != '.' )
														{
															$key3 = '.' . $key3;
														}
														$key3 = $key2 . $key3;
													}
													$competency3 = new stdClass();
													$competency3->level = 3;
													$competency3->sublevels = 0;
													$competency3->label = trim($competencyLevel3->Prefix);
													$competency3->text = str_replace(array('[b]', '[/b]', '[i]', '[/i]', "\\r\\n", "\\n"), array('<b>', '</b>', '<i>', '</i>', '<br>', '<br>'), htmlspecialchars(trim($competencyLevel3->DescriptionText), ENT_QUOTES|ENT_HTML5));
													$competency3->competencyLevel = 0;
													if ( isset($competencyLevel3->IsTicked) && $competencyLevel3->IsTicked == 'true' )
													{
														$data->learningOutcomesMapping[$learningOutcomeNumber][$key3] = 1;
														if ( isset($competencyLevel3->DL) )
														{
															$data->learningOutcomesMapping[$learningOutcomeNumber][$key3] = 0 + $competencyLevel3->DL;
															if ( $data->learningOutcomesMapping[$learningOutcomeNumber][$key3] > $DL3 )
															{
																$DL3 = $data->learningOutcomesMapping[$learningOutcomeNumber][$key3];
															}
														}
														$mapping3 = true;
													}
													$data->competencies[$key3] = $competency3;
												}
												if ( $mapping3 && !isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key2]) )
												{
													$data->learningOutcomesMapping[$learningOutcomeNumber][$key2] = 1;
												}
												if ( isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key2]) && $DL3 > $data->learningOutcomesMapping[$learningOutcomeNumber][$key2] )
												{
													$data->learningOutcomesMapping[$learningOutcomeNumber][$key2] = $DL3;
												}
												if ( $DL3 > $DL2 )
												{
													$DL2 = $DL3;
												}
											}
											if ( $mapping2 && !isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key1])  )
											{
												$data->learningOutcomesMapping[$learningOutcomeNumber][$key1] = 1;
											}
											if ( isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key1]) && $DL2 > $data->learningOutcomesMapping[$learningOutcomeNumber][$key1] )
											{
												$data->learningOutcomesMapping[$learningOutcomeNumber][$key1] = $DL2;
											}
										}
									}
								}
							}
							$learningOutcomeNumber++;
						}
					}
					foreach ($data->learningOutcomesMapping as $learningOutcomeNumber => $learningOutcomeMapping)
					{
						foreach ($learningOutcomeMapping as $key => $DL)
						{
							if ( $data->competencies[$key]->competencyLevel <= $DL )
							{
								$data->competencies[$key]->competencyLevel = $DL;
							}
							if ( $type == 'fdd' && substr($key, 0, 1) == '2' )
							{
								$data->competencies[$key]->competencyLevel = 0;
								$data->learningOutcomesMapping[$learningOutcomeNumber][$key] = 0;
							}
						}
					}
					// get assessment mappings
					if ( $type == 'course' && isset($rawXmlData->MyAssessmentMapping) && $rawXmlData->MyAssessmentMapping && isset($rawXmlData->MyAssessmentMapping->AllAssessment) && $rawXmlData->MyAssessmentMapping->AllAssessment )
					{
						$assessmentNumber = 0;
						foreach ($rawXmlData->MyAssessmentMapping->AllAssessment->children() as $assessmentItem)
						{
							$assessment = new stdClass();
							$assessment->name = htmlspecialchars($assessmentItem->PoaName, ENT_QUOTES|ENT_HTML5);
							$assessment->typeCode = 0 + $assessmentItem->PoaType;
							$assessment->weight = 0 + $assessmentItem->PoaWeight;
							$data->assessments[$assessmentNumber] = $assessment;
							if ( isset($assessmentItem->PoaBreakdown) && $assessmentItem->PoaBreakdown )
							{
								foreach ($assessmentItem->PoaBreakdown->children() as $valN => $weight)
								{
									$learningOutcomeN = substr($valN, 3) - 1;
									$data->assessmentsMapping[$assessmentNumber][$learningOutcomeN] = 0 + $weight;
								}
							}
							$assessmentNumber++;
						}
					}
				}
			}
		}
	}
	if ( $detail != 'basic' )
	{
		$mappingFile = './' . $dataDirectory . '/' . $data->fileName . 'MappingResult.xml';
		if ( file_exists($mappingFile) )
		{
			$rawXmlData = simplexml_load_file($mappingFile);
			if ( $rawXmlData )
			{
				if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) )
				{
					// get assessment mapping results
					if ( $type == 'course' )
					{
						$assessmentTypes = listAssessmentTypes();
						foreach ($assessmentTypes as $typeN => $assessmentType)
						{
							$data->assessmentCategorisationSummary[$typeN] = 0.0;
						}
						if ( isset($rawXmlData->AssessmentCategorisation) && $rawXmlData->AssessmentCategorisation )
						{
							$typeN = 0;
							foreach ($rawXmlData->AssessmentCategorisation->children() as $assessmentCategorisationSummary)
							{
								if ( isset($data->assessmentCategorisationSummary[$typeN]) )
								{
									$data->assessmentCategorisationSummary[$typeN] = 0.0 + $assessmentCategorisationSummary;
								}
								$typeN++;
							}
						}
					}
					if ( isset($rawXmlData->MappingPlotData1) && $rawXmlData->MappingPlotData1 )
					{
						foreach ($rawXmlData->MappingPlotData1->children() as $item)
						{
							$key = '' . $item->key->children()[0];
							$data->mappingData[$key] = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
							$v = 1;
							foreach ($item->value->ArrayOfDouble->children() as $value)
							{
								if ( $type != 'fdd' || substr($key, 0, 1) != '2' )
								{
									$data->mappingData[$key][$v] = 0.0 + $value;
									if ( $type == 'fdd' )
									{
										$data->units += $data->mappingData[$key][$v];
									}
								}
								$v++;
							}
						}
					}
				}
			}
		}
	}
	
	return $data;
}





// This function prints HTML for a single course
function displayCoursePage($name, $course, $urlPrefix, $accreditationDisplayScript)
{
	echo PHP_EOL . '<!-- begin course information -->' . PHP_EOL . PHP_EOL;
	echo '<main>' . PHP_EOL;
	echo '	<section>' . PHP_EOL;
	echo '		<h2>' . $name . '</h2>' . PHP_EOL;
	echo '		<div class="alert alert-info fst-italic" role="alert">Note: information provided here is indicative only. For full and current course information view the official page on P&amp;C.</div>' . PHP_EOL;
	echo '		<table class="table">' . PHP_EOL;
	echo '			<tbody>' . PHP_EOL;
	echo '				<tr><th>code: </th><td>' . $course->code . '</td></tr>' . PHP_EOL;
	echo '				<tr><th>name: </th><td class="fst-italic">' . $course->name . '</td></tr>' . PHP_EOL;
	if ( $accreditationDisplayScript )
	{
		echo '				<tr><th><i>JCUMap</i> files: </th><td><ul class="m-0">';
		if ( file_exists('courses/' . $course->fileName . '.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'courses/' . $course->fileName . '.xml" download>' . $course->fileName . '.xml</a></li>';
		}
		if ( file_exists('courses/' . $course->fileName . 'MappingResult.xml') )
		{
			echo '<li><a href="' . $urlPrefix . 'courses/' . $course->fileName . 'MappingResult.xml" download>' . $course->fileName . 'MappingResult.xml</a></li>';
		}
		echo '</ul></td></tr>' . PHP_EOL;
	}				
	if ( isset($course->units) )
	{
		echo '				<tr><th>unit value: </th><td>' . number_format($course->units, 0) . '</td></tr>' . PHP_EOL;
	}
	if ( isset($course->description) && $course->description )
	{
		echo '				<tr><th>description: </th><td class="small">' . str_replace("\n", '<br> ', str_replace("\r\n", '<br> ', trim($course->description))) . '</td></tr>' . PHP_EOL;
	}
	echo '				<tr><th>P&amp;C: </th><td class="position-relative"><a class="stretched-link" href="' . generateLinkToProgramsAndCourses($course->code) . '">' . generateLinkToProgramsAndCourses($course->code) . '</a></td></tr>' . PHP_EOL;
	if ( isset($course->learningOutcomes) && $course->learningOutcomes )
	{
		echo '				<tr><th>course learning outcomes: </th><td class="small"><ol>';
		foreach ($course->learningOutcomes as $learningOutcome)
		{
			if ( $learningOutcome )
			{
				echo '<li>' . $learningOutcome . '</li>';
			}
		}
		echo '</ol></td></tr>' . PHP_EOL;
	}
	if ( isset($course->assessments) && $course->assessments )
	{
		echo '				<tr><th>assessment: </th><td class="small">';
		if ( $accreditationDisplayScript )
		{
			$assessmentTypes = listAssessmentTypes();
			echo '<table class="table table-sm table-bordered table-hover caption-top"><caption class="fst-italic">assessment breakdown</caption>';
			echo '<colgroup><col span="1"><col span="1"><col span="1"><col span="' . count($course->learningOutcomes) . '"></colgroup>';
			echo '<thead class="bg-light"><tr><th rowspan="2">assessment item(s)</th><th rowspan="2">category</th><th rowspan="2" class="text-center">weight</th><th colspan="' . count($course->learningOutcomes) . '" class="text-center">percentage breakdown (per 100% item)</th></tr><tr>';
			foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
			{
				echo '<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="' . ( $learningOutcomeN + 1 ). '. ' . $learningOutcome . '">' . ( $learningOutcomeN + 1 ). '</th>';
			}
			echo '</tr></thead><tbody>';
			foreach ($course->assessments as $assessmentN => $assessment)
			{							
				echo '<tr><td><ol class="m-0" start="' . ( $assessmentN + 1 ) . '"><li>' . $assessment->name . '</li></ol></td><td>' . $assessmentTypes[$assessment->typeCode]->type . '</td><td class="text-center fw-bold">';
				if ( $assessment->weight > 0 )
				{
					echo $assessment->weight . '%';
				}
				echo '</td>';
				foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
				{
					echo '<td class="text-center">';
					if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) && $course->assessmentsMapping[$assessmentN][$learningOutcomeN] > 0)
					{
						echo $course->assessmentsMapping[$assessmentN][$learningOutcomeN] . '%';
					}
					echo '</td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
			if ( $course->assessmentCategorisationSummary )
			{
				$assessmentTotals = array_sum($course->assessmentCategorisationSummary);
				echo '<table class="table table-sm table-bordered table-hover caption-top"><caption class="fst-italic">assessment types used across whole subject</caption>';
				echo '<thead class="bg-light"><tr><th>assessment type</th><th colspan="2" class="text-center">contribution to overall assessment</th></tr></thead><tbody>';
				foreach ($course->assessmentCategorisationSummary as $assessmentType => $assessmentTypeCredits)
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
			}
		}
		else
		{
			echo '<ol>';
			foreach ($course->assessments as $assessment)
			{
				echo '<li>' . $assessment->name;
				if ( $assessment->weight > 0 )
				{
					echo ' (' . $assessment->weight . '%)';
				}
				echo '</li>';
			}
			echo '</ol>';
		}
		echo '</td></tr>' . PHP_EOL;
	}
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	
	// table of all learning outcomes mappings (to EA competencies and to assessment items)
	echo '		<section>' . PHP_EOL;
	echo '			<h3>Mapped learning outcomes</h3>' . PHP_EOL;
	echo '			<table class="table table-sm table-bordered table-hover small">' . PHP_EOL;
	echo '				<colgroup>' . PHP_EOL;
	echo '					<col span="1">' . PHP_EOL;
	foreach ($course->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '					<col span="' . $competency->sublevels . '">' . PHP_EOL;
		}
	}
	if ( $course->assessments )
	{
		echo '					<col span="' . count($course->assessments) . '">' . PHP_EOL;
	}
	echo '				</colgroup>' . PHP_EOL;
	echo '				<thead class="bg-light">' . PHP_EOL;
	echo '					<tr><th rowspan="2" class="text-center align-middle">learning outcome</th>';
	foreach ($course->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
		echo '<th colspan="' . $competency->sublevels . '" class="text-center">' . $competency->label . ' ' . $competency->text . '</th>';
		}
	}
	if ( $course->assessments )
	{
		echo '<th colspan="' . count($course->assessments) . '" class="text-center">assessment tasks</th>';
	}
	echo '</tr>' . PHP_EOL;
	echo '					<tr>';
	foreach ($course->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 2 )
		{
			echo '<th class="text-center align-middle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</th>';
		}
	}
	if ( $course->assessments )
	{
		foreach ($course->assessments as $assessmentN => $assessment)
		{
			echo '<th class="text-center align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $assessment->name;
			if ( $accreditationDisplayScript )
			{
				echo ' (' . $assessment->weight . '%)';
			}
			echo '">' . ( $assessmentN + 1 ) . '</th>';
		}
	}
	echo '</tr>' . PHP_EOL;
	echo '				</thead>' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	foreach ($course->learningOutcomes as $learningOutcomeN => $learningOutcome)
	{
		if ( $learningOutcome )
		{
			echo '					<tr><td><ol class="m-0" start="' . ( $learningOutcomeN + 1 ) . '"><li>' . $learningOutcome . '</li></ol></td>';
			foreach ($course->competencies as $competencyKey => $competency)
			{
				if ( $competency->level == 2 )
				{
					echo '<td class="text-center text-success align-middle">';
					if ( isset($course->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $course->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0)
					{
						if ( $accreditationDisplayScript )
						{
							for ($i=0; $i<$course->learningOutcomesMapping[$learningOutcomeN][$competencyKey]; $i++)
							{
								echo '✓';
							}
						}
						else
						{
							echo '✓';
						}
					}
					echo '</td>';
				}
			}
			if ( $course->assessments )
			{
				foreach ($course->assessments as $assessmentN => $assessment)
				{
					echo '<td class="text-center text-success align-middle"';
					if ( $accreditationDisplayScript )
					{
						echo ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="';
						if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) )
						{
							echo $course->assessmentsMapping[$assessmentN][$learningOutcomeN];
						}
						else
						{
							echo '0';
						}
						echo '% of assessment #' . ( $assessmentN + 1 ) . '"';
					}
					echo '>';
					if ( isset($course->assessmentsMapping[$assessmentN][$learningOutcomeN]) && $course->assessmentsMapping[$assessmentN][$learningOutcomeN] > 0)
					{
						echo '✓';
					}
					echo '</td>';
				}
			}
			echo '</tr>' . PHP_EOL;
		}
	}
	echo '				</tbody>' . PHP_EOL;
	echo '			</table>' . PHP_EOL;
	echo '		</section>' . PHP_EOL;
	
	// display chart of development level learning against each of the competencies
	echo '		<section>' . PHP_EOL;
	echo '			<h3>Course contribution towards the ' . $course->competencyName . '</h3>' . PHP_EOL;
	if ( $accreditationDisplayScript )
	{
		echo '			<p>This table maps how the unit credits of this course contribute towards achievement of the ' . $course->competencyName . '.</p>' . PHP_EOL;
	}
	else
	{
		echo '			<p>This table depicts the relative contribution of this course towards the ' . $course->competencyName . '. <em>Note that this illustration is indicative only, and may not take into account any recent changes to the course. You are advised to review the official course page on P&amp;C for current information.</em>.</p>' . PHP_EOL;
	}
	$maxUnits = 1;
	foreach ($course->mappingData as $competencyLabel => $DLs)
	{
		$maxUnits = max($maxUnits, ceil(array_sum($DLs)));
	}
	echo '			<table class="table table-sm table-hover small">' . PHP_EOL;
	$colSpan = 1;
	if ( $accreditationDisplayScript )
	{
		$colSpan = 2;
		echo '				<thead class="bg-light sticky-top">' . PHP_EOL;
		echo '					<tr><th class="col-1"></th><th class="text-start border-start">0.0</th><th class="text-end border-end">' . $maxUnits . '.0</th></tr>' . PHP_EOL;
		echo '				</thead>' . PHP_EOL;
	}
	echo '				<tbody>' . PHP_EOL;
	foreach ($course->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '					<tr class="table-secondary"><td colspan="' . ( $colSpan + 1 ) . '" class="fw-bold">' . $competency->label . ' ' . $competency->text . '</td></tr>' . PHP_EOL;
		}
		else if ( $competency->level == 2 )
		{
			echo '					<tr class="border-bottom"><td class="text-center align-middle border-end col-1" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</td><td colspan="' . $colSpan . '" class="align-middle">' . PHP_EOL;
			echo '						<div class="progress bg-transparent">';
			if ( $accreditationDisplayScript )
			{
				if ( isset($course->mappingData[$competency->label][1]) && $course->mappingData[$competency->label][1] > 0.0)
				{
					$mappingPercentage = 100 * $course->mappingData[$competency->label][1] / $maxUnits;
					echo '<div class="progress-bar bg-success text-start" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $course->mappingData[$competency->label][1] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($course->mappingData[$competency->label][1], 2) . '">DL1</div>';
				}
				if ( isset($course->mappingData[$competency->label][2]) && $course->mappingData[$competency->label][2] > 0.0)
				{
					$mappingPercentage = 100 * $course->mappingData[$competency->label][2] / $maxUnits;
					echo '<div class="progress-bar bg-primary text-center" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $course->mappingData[$competency->label][2] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($course->mappingData[$competency->label][2], 2) . '">DL2</div>';
				}
				if ( isset($course->mappingData[$competency->label][3]) && $course->mappingData[$competency->label][3] > 0.0)
				{
					$mappingPercentage = 100 * $course->mappingData[$competency->label][3] / $maxUnits;
					echo '<div class="progress-bar bg-danger text-end" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $course->mappingData[$competency->label][3] . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($course->mappingData[$competency->label][3], 2) . '">DL3</div>';
				}
			}
			else
			{
				$sumOfUnits = array_sum($course->mappingData[$competency->label]);
				if ( $sumOfUnits > 0.0 )
				{
					$mappingPercentage = 100 * $sumOfUnits / $maxUnits;
					echo '<div class="progress-bar" role="progressbar" style="width: ' . $mappingPercentage . '%"></div>';
				}
			}
			echo '</div>' . PHP_EOL;
			echo '					</td></tr>' . PHP_EOL;
		}
	}
	echo '				</tbody>' . PHP_EOL;
	echo '			</table>' . PHP_EOL;
	echo '		</section>' . PHP_EOL;
	
	// list all EA competencies, and indicate which are addressed in this course
	echo '		<section>' . PHP_EOL;
	echo '			<h3>' . $course->competencyName . ' — summary</h3>' . PHP_EOL;
	echo '			<table class="table table-sm table-hover">' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	$colSpan = 0;
	if ( $accreditationDisplayScript )
	{
		$colSpan = 1;
	}
	foreach ($course->competencies as $competencyKey => $competency)
	{
		switch ($competency->level)
		{
			case 1:
				echo '					<tr class="table-secondary">';
				echo '<th colspan="' . ( 3 + $colSpan ) . '">' . $competency->label . ' ' . $competency->text . '</th>';
				echo '</tr>' . PHP_EOL;
				break;
			case 2:
				echo '					<tr class="small">';
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
				if ($accreditationDisplayScript)
				{
					echo '					<tr class="small">';
					if ( $colSpan)
					{
						echo '<td colspan="' . $colSpan . '" class="small"></td>';
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
						echo 'align-top small">✓</td>';
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
	echo '	</section>' . PHP_EOL;
	echo '</main>' . PHP_EOL;
	echo PHP_EOL . '<!-- end course information -->' . PHP_EOL . PHP_EOL;
}
