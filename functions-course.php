<?php
/*
	functions-course.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/





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
