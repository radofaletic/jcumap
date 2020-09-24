<?php
/*
	jcumap-output-processor.php

	by © 2020, Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

function biBoxArrowUpRight()
{
	return '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-box-arrow-up-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>';
}

function getDefinition($code)
{
	$jsonFile = "./definitions/" . $code . ".json";
	$json = false;
	if ( file_exists($jsonFile) )
	{
		$rawJsonData = file_get_contents($jsonFile);
		$json = json_decode($rawJsonData);
	}
	return $json;
}

function getCourse($code, $detail = 'basic', $type = 'course')
{
	$data = new stdClass();
	$data->code = $code;
	$data->name = "";
	$data->description = "";
	$data->units = 0;
	$data->competencyName = "";
	$data->competencies = array();
	$data->learningOutcomes = array();
	$data->learningOutcomesMapping = array();
	$data->assessments = array();
	$data->assessmentsMapping = array();
	$data->assessmentCategorisationSummary = array();
	$data->mappingData = array();
	
	$dataDirectory = "courses";
	if ( $type == "program" || $type == "fdd" )
	{
		$dataDirectory = "programs";
	}
	
	$dataFile = "./" . $dataDirectory . "/" . $code . ".xml";
	
	if ( file_exists($dataFile) )
	{
		$rawXmlData = simplexml_load_file($dataFile);
		if ( $rawXmlData )
		{
			if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) == $data->code )
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
										$key1 = "" . trim($competencyLevel1->Prefix);
										$competency1 = new stdClass();
										$competency1->level = 1;
										$competency1->sublevels = count($competencyLevel1->ChildCompetencies);
										$competency1->label = trim($competencyLevel1->Prefix);
										$competency1->text = str_replace(array("[b]", "[/b]", "[i]", "[/i]", "\\r\\n", "\\n"), array("<b>", "</b>", "<i>", "</i>", "<br>", "<br>"), htmlspecialchars(trim($competencyLevel1->DescriptionText), ENT_QUOTES|ENT_HTML5));
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
												$key2 = "" . trim($competencyLevel2->Prefix);
												if ( substr($key2, 0, strlen($key1)) != $key1 )
												{
													if ( substr($key1, 0, -1) != "." )
													{
														$key2 = "." . $key2;
													}
													$key2 = $key1 . $key2;
												}
												$competency2 = new stdClass();
												$competency2->level = 2;
												$competency2->sublevels = count($competencyLevel2->ChildCompetencies);
												$competency2->label = trim($competencyLevel2->Prefix);
												$competency2->text = str_replace(array("[b]", "[/b]", "[i]", "[/i]", "\\r\\n", "\\n"), array("<b>", "</b>", "<i>", "</i>", "<br>", "<br>"), htmlspecialchars(trim($competencyLevel2->DescriptionText), ENT_QUOTES|ENT_HTML5));
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
													$key3 = "" . trim($competencyLevel3->Prefix);
													if ( substr($key3, 0, strlen($key2)) != $key2 )
													{
														if ( substr($key2, 0, -1) != "." )
														{
															$key3 = "." . $key3;
														}
														$key3 = $key2 . $key3;
													}
													$competency3 = new stdClass();
													$competency3->level = 3;
													$competency3->sublevels = 0;
													$competency3->label = trim($competencyLevel3->Prefix);
													$competency3->text = str_replace(array("[b]", "[/b]", "[i]", "[/i]", "\\r\\n", "\\n"), array("<b>", "</b>", "<i>", "</i>", "<br>", "<br>"), htmlspecialchars(trim($competencyLevel3->DescriptionText), ENT_QUOTES|ENT_HTML5));
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
							if ( $type == 'fdd' && substr($key, 0, 1) == "2" )
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
		$mappingFile = "./" . $dataDirectory . "/" . $code . "MappingResult.xml";
		if ( file_exists($mappingFile) )
		{
			$rawXmlData = simplexml_load_file($mappingFile);
			if ( $rawXmlData )
			{
				if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) == $data->code )
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
							$key = "" . $item->key->children()[0];
							$data->mappingData[$key] = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
							$v = 1;
							foreach ($item->value->ArrayOfDouble->children() as $value)
							{
								if ( $type != 'fdd' || substr($key, 0, 1) != "2" )
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

function getProgram($code, $details = 'basic')
{
	return getCourse($code, $details, 'program');
}

function getFDD($code, $details = 'basic')
{
	return getCourse($code, $details, 'fdd');
}

function listAllCourseCodes()
{
	$courses = array();
	$directory = opendir("./courses");
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 12 <= strlen($filename) && ctype_upper( substr($filename, 0, 4) ) && ctype_digit( substr($filename, 4, 4) ) && substr($filename, -4) == ".xml" && substr($filename, -17) != "MappingResult.xml" )
		{
			$courseCode = substr($filename, 0, 8);
			$courses[$courseCode] = $courseCode;
		}
    }
    ksort($courses);
    return $courses;
}

function listAllPrograms()
{
	$programs = array();
	$directory = opendir("./programs");
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 7 <= strlen($filename) && ctype_upper( substr($filename, 0, -4) ) && substr($filename, -4) == ".xml" && substr($filename, -17) != "MappingResult.xml" )
		{
			$program = substr($filename, 0, -4);
			$programs[$program] = $program;
		}
    }
    ksort($programs);
    return $programs;
}

function listAssessmentTypes()
{
	$types = array();
	
	$type = new stdClass();
	$type->category = "Exam";
	$type->type = "Test/Exam (Invigilated)";
	$types[0] = $type;
	
	$type = new stdClass();
	$type->category = "Exam";
	$type->type = "Test/Quiz (Non-Invigilated)";
	$types[1] = $type;
	
	$type = new stdClass();
	$type->category = "Exam";
	$type->type = "Skill Test (Demonstration/Laboratory/Studio/Clinic/Field/Other)";
	$types[2] = $type;
	
	$type = new stdClass();
	$type->category = "Exam";
	$type->type = "Objective Structured Clinical Examination";
	$types[3] = $type;
	
	$type = new stdClass();
	$type->category = "Oral &amp; Performance";
	$type->type = "Creative Work";
	$types[4] = $type;
	
	$type = new stdClass();
	$type->category = "Oral &amp; Performance";
	$type->type = "Participation/Leadership";
	$types[5] = $type;
	
	$type = new stdClass();
	$type->category = "Oral &amp; Performance";
	$type->type = "Performance (Artistic/Exhibition/Moot Court/Other)";
	$types[6] = $type;
	
	$type = new stdClass();
	$type->category = "Oral &amp; Performance";
	$type->type = "Presentation (Seminar/Debate/Forum/Critique/Other)";
	$types[7] = $type;
	
	$type = new stdClass();
	$type->category = "Oral &amp; Performance";
	$type->type = "Teamwork Performance Evaluation";
	$types[8] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Dissertation/Thesis/Research Paper";
	$types[9] = $type;
	
	$type->category = "Written Discourse";
	$type->type = "Journal (Field/WIL/Laboratory/Reflective/Other)";
	$types[10] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Portfolio";
	$types[11] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Poster";
	$types[12] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Proposal";
	$types[13] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Report (Experimental/Analytical)";
	$types[14] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Report (Project/Design/Research)";
	$types[15] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Review (Literature/Critical)";
	$types[16] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Tutorial Submission/Workbook/Logbook";
	$types[17] = $type;
	
	$type = new stdClass();
	$type->category = "Written Discourse";
	$type->type = "Other Writing (Abstract/Annotated Bibliography/Case Study/Essay/Other)";
	$types[18] = $type;
	
	$type = new stdClass();
	$type->category = "Vocational";
	$type->type = "Professional Practice (Planning/Execution/Report)";
	$types[19] = $type;
	
	$type = new stdClass();
	$type->category = "Vocational";
	$type->type = "Software/Manufactured Design/Other Physical Output";
	$types[20] = $type;
	
	return $types;
}

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
