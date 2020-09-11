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

function getCourse($code, $detail = 'basic')
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
	$data->result = false;
	
	$dataFile = "./jcumap-output-files/" . $code . "_Data.xml";
	
	if ( file_exists($dataFile) )
	{
		$rawXmlData = simplexml_load_file($dataFile);
		if ( $rawXmlData )
		{
			if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) == $data->code )
			{
				$data->name = trim($rawXmlData->SubjectInfo->SubjectName);
				$data->description = trim($rawXmlData->SubjectInfo->SubjectDescription);
				$data->units = trim($rawXmlData->SubjectInfo->CreditPoints);
				if ( $detail != 'basic' )
				{
					$data->data = $rawXmlData;
					if ( isset($rawXmlData->SLOCount) && $rawXmlData->SLOCount > 0 && isset($rawXmlData->SLOsAndMapping) )
					{
						$learningOutcomeNumber = 0;
						foreach ($rawXmlData->SLOsAndMapping->children() as $mapping)
						{
							$data->learningOutcomes[$learningOutcomeNumber] = htmlspecialchars(trim($mapping->SubjectLearningOutcome), ENT_QUOTES|ENT_HTML5);
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
												}
												$mapping2 = true;
											}
											$data->competencies[$key2] = $competency2;
											$mapping3 = false;
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
													}
													$mapping3 = true;
												}
												$data->competencies[$key3] = $competency3;
											}
											if ( $mapping3 && !isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key2]) )
											{
												$data->learningOutcomesMapping[$learningOutcomeNumber][$key2] = 1;
											}
										}
										if ( $mapping2 && !isset($data->learningOutcomesMapping[$learningOutcomeNumber][$key1])  )
										{
											$data->learningOutcomesMapping[$learningOutcomeNumber][$key1] = 1;
										}
									}
								}
							}
							$learningOutcomeNumber++;
						}
					}
					foreach ($data->learningOutcomesMapping as $learningOutcomeMapping)
					{
						foreach ($learningOutcomeMapping as $key => $DL)
						{
							if ( $data->competencies[$key]->competencyLevel <= $DL )
							{
								$data->competencies[$key]->competencyLevel = $DL;
							}
						}
					}
					if ( isset($rawXmlData->MyAssessmentMapping) && $rawXmlData->MyAssessmentMapping && isset($rawXmlData->MyAssessmentMapping->AllAssessment) && $rawXmlData->MyAssessmentMapping->AllAssessment )
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
		$mappingFile = "./jcumap-output-files/" . $code . "_Result.xml";
		if ( file_exists($mappingFile) )
		{
			$rawXmlData = simplexml_load_file($mappingFile);
			if ( $rawXmlData )
			{
				if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) == $data->code )
				{
					$data->result = $rawXmlData;
				}
			}
		}
	}
	
	return $data;
}

function getAllCourseCodes()
{
	$courses = array();
	$directory = opendir("./jcumap-output-files");
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 12 <= strlen($filename) && ctype_upper( substr($filename, 0, 4) ) && ctype_digit( substr($filename, 4, 4) ) && substr($filename, -4) == ".xml" )
		{
			$courseCode = substr($filename, 0, 8);
			$courses[$courseCode] = substr($filename, 0, 8);
		}
    }
    return $courses;
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
	else if ( strlen($code) == 5 && ctype_upper($code) )
	{
		$url .= $programPrefix . "/" . $code;
	}
	return $url;
}

function generateHTMLfromJCUMap($inputFile, $outputType = "public")
{
	
	
}
