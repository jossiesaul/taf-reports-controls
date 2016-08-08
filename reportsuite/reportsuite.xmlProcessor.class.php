<?php

/**
 * Description of reportsuite
 *
 * @author jossie.saul <jossie.saul@datalex.com>
 * @copyright (c) Datalex, Plc 2013
 */
class reportsuiteXmlProcessor {
   
    /**
     *
     * Result elemenet Namespace
     * 
     * @var type String
     */
    private static $resultElNS = 'resultExt';
    
    /**
     * Default Array Var for Test Results data
     * 
     * @var Array
     */
    public static $testResultData = array();
    
       
    public static function getScenariosNameInSuiteByXml($xmlObj)
    {
        $scenarios = array();
        foreach ($xmlObj->testScenarios->TestScenario as $scenario)
        {
            $scenarios[] = $scenario->name;
        }   
        return $scenarios;
    }
    
    public static function getOverallResultsFromTestCasesInScenario($testScenario)
    {
        $resArr = array('Passed' => 0,
                        'Warning' => 0,
                        'Failed' => 0,
                        'TAFError' => 0);
        
        foreach($testScenario->testCases->TestCase as $testCase)
        {
            if (isset($testCase->reportSuiteAddit->overrideTcOutcomeFlag) &&
               $testCase->reportSuiteAddit->overrideTcOutcomeFlag == TRUE)
            {
                $resArr[(String)$testCase->reportSuiteAddit->overrideTcOutcomeValue]++;
            }
            else
            {
                $resArr[(String)$testCase->status]++;
            }
        }
        return $resArr;
    }


    /**
     * 
     * @param type $xmlObj
     * @param type $scenarioName If Set, only select the scenario
     * @return type
     */
    public static function getOverallResultsFromScenariosInSuite($xmlObj, $scenarioName = NULL)
    {
        $resArr = array('Passed' => 0,
                        'Warning' => 0,
                        'Failed' => 0,
                        'TAFError' => 0);
        
        foreach ($xmlObj->testScenarios->TestScenario as $testScenario)
        {
            //drupal_set_message($scenarioName.'=='.$testScenario->name);
            if($scenarioName != NULL)
            {
                if($scenarioName === (String)$testScenario->name)
                {
                    if (isset($testScenario->reportSuiteAddit->outcomeOverrideFlag) &&
                        $testScenario->reportSuiteAddit->outcomeOverrideFlag == TRUE)
                    {
                        $resArr['Passed'] += $testScenario->reportSuiteAddit->passed;
                        $resArr['Warning'] += $testScenario->reportSuiteAddit->warning;
                        $resArr['Failed'] += $testScenario->reportSuiteAddit->failed;
                        $resArr['TAFError'] += $testScenario->reportSuiteAddit->tafError;
                    }
                    else
                    {
                        $resArr['Passed'] += $testScenario->passed;
                        $resArr['Warning'] += $testScenario->warning;
                        $resArr['Failed'] += $testScenario->failed;
                        $resArr['TAFError'] += $testScenario->error;
                    }
                }
            }
            else
            {
                if (isset($testScenario->reportSuiteAddit->outcomeOverrideFlag) &&
                    $testScenario->reportSuiteAddit->outcomeOverrideFlag == TRUE)
                {
                    $resArr['Passed'] += $testScenario->reportSuiteAddit->passed;
                    $resArr['Warning'] += $testScenario->reportSuiteAddit->warning;
                    $resArr['Failed'] += $testScenario->reportSuiteAddit->failed;
                    $resArr['TAFError'] += $testScenario->reportSuiteAddit->tafError;
                }
                else
                {
                    $resArr['Passed'] += $testScenario->passed;
                    $resArr['Warning'] += $testScenario->warning;
                    $resArr['Failed'] += $testScenario->failed;
                    $resArr['TAFError'] += $testScenario->error;
                }
            }
            
        }
        return $resArr;
    }
   
    public static function getOverallResultsFromSuites($xmlObj)
    {
        $resArr = array('Passed' => 0,
                        'Warning' => 0,
                        'Failed' => 0,
                        'TAFError' => 0);
        
        if (isset($xmlObj->reportSuiteAddit->outcomeOverrideFlag) &&
               $xmlObj->reportSuiteAddit->outcomeOverrideFlag == TRUE)
        {
            $resArr['Passed'] = $xmlObj->reportSuiteAddit->passed;
            $resArr['Warning'] = $xmlObj->reportSuiteAddit->warning;
            $resArr['Failed'] = $xmlObj->reportSuiteAddit->failed;
            $resArr['TAFError'] = $xmlObj->reportSuiteAddit->tafError;
        }
        else
        {
            $resArr['Passed'] = $xmlObj->passed;
            $resArr['Warning'] = $xmlObj->warning;
            $resArr['Failed'] = $xmlObj->failed;
            $resArr['TAFError'] = $xmlObj->error;
        }
        return $resArr;
    }
    
    public static function getCoverageMatrixCatalog()
    {
        return array('Flight Type' => array(),
                     'Search Type' => array(),
                     'PAX Type' => array(), 
                     'Ancillaries' => array(),
                     'Payment Type' => array(),
                     'Basic Features' => array(),
            );
    }
    
    
    /**
     * 
     * Grab the Route Result Number
     * 
     * @param type $specifiedOutcome
     * @param type $xmlObj
     * @return specifiedOutcome Description
     * 
     */
    public static function getSpecifiedOverallResult($xmlObj, $specifiedOutcome)
    {
        $result = '';
        
        if(empty($xmlObj)) return $result;
        
        if(isset($xmlObj->reportSuiteAddit->outcomeOverrideFlag) &&
           $xmlObj->reportSuiteAddit->outcomeOverrideFlag == TRUE)
        {
            if($specifiedOutcome === 'error')
            {
                $specifiedOutcome = 'tafError';
            }
            $result = (String)$xmlObj->reportSuiteAddit->$specifiedOutcome;
        }
        else
        {
            if($specifiedOutcome === 'tafError')
            {
                $specifiedOutcome = 'error';
            }
            $result = (String)$xmlObj->$specifiedOutcome;
        }
        
        return $result;
    }

    public static function getOutcomeInfoByTestCase($testCaseXmlObj)
    {
        $outcomeInfo = array('status' => '',
                             'detail' => '',
                       );
        if(isset($testCaseXmlObj->reportSuiteAddit->overrideTcOutcomeFlag) &&
           $testCaseXmlObj->reportSuiteAddit->overrideTcOutcomeFlag == TRUE)
        {
            $outcomeInfo['status'] = (String)$testCaseXmlObj->reportSuiteAddit->overrideTcOutcomeValue;
            $outcomeInfo['detail'] = (String)$testCaseXmlObj->reportSuiteAddit->overrideTcOutcomeReason;
            $outcomeInfo['originalStatus'] = (String)$testCaseXmlObj->status;
            $outcomeInfo['originalDetail'] = (String)$testCaseXmlObj->details;
        }
        else
        {
            $outcomeInfo['status'] = (String)$testCaseXmlObj->status;
            $outcomeInfo['detail'] = (String)$testCaseXmlObj->details;
        }
        
        return $outcomeInfo;
    }

    public static function getFailedTestCases($testResultData)
    {
        $failedTestCases = array();
        unset($testResultData['uuid']);
        foreach ($testResultData as $scenario => $testCaseResult)
        {
            foreach ($testCaseResult as $tcId => $resultData)
            {
                if ($resultData['outcome'] == 'Failed')
                {
                    $failedTestCases[$scenario][$tcId] = $resultData;
                }
            }
        }
        return $failedTestCases;
    }
    
    public static function getParentChildElementByElementName($xmlObj, $elementName)
    {
        return (String) $xmlObj->$elementName;
    }
    
    public static function gatherRerunOnCase($orgReid, $testCase, $orderBy = 'LatestReid')
    {
        $rerData = array(
            'MASTER' =>array(
                'reid' => $orgReid,
                'id' => (String)$testCase->id,
                'description' => (String)$testCase->description,
                'status' => (String)$testCase->status,
                'comment' => (String)$testCase->comment,
                'details' => (String)$testCase->details,
                'executionTime' => (String)$testCase->executionTime,
            )
        );
        if(isset($testCase->reservationNumber))
                         $rerData['MASTER']['reservationNumber'] = (String)$testCase->reservationNumber;
        #TODO: Orderby algorithm patch 
        $latestReid = 0;
        //drupal_set_message('<pre>' . print_r($testCase->reRunData, 1) . '</pre>');
        foreach ($testCase->reRunData as $rrdata)
        {
            foreach($rrdata as $rreid => $data)
            {
                $newReid = (String)$data->reid;
                if($newReid > $latestReid)
                {
                    if(isset($rerData['latest']))
                        $rerData['latest'][$rerData['latest']['rried']] = $rerData['latest'];
                    
                    $latestReid = $newReid;
                    $rerData['latest'] = array(
                        'rreid' => $rreid,
                        'reid' => $newReid,
                         'status' => (String)$data->status,
                         'comment' => (String)$data->comment,
                         'details' => (String)$data->details,
                         'executionTime' => (String)$data->executionTime,
                         'rerunDate' => (String)$data->rerunDate,
                     );
                        
                     if(isset($data->reservationNumber))
                         $rerData['latest']['reservationNumber'] = (String)$data->reservationNumber;
                        
                }
                else
                {
                        $rerData['record'][$rreid] = array(
                          'rreid' => $rreid,
                          'reid' => $newReid,
                          'status' => (String)$data->status,
                          'comment' => (String)$data->comment,
                          'details' => (String)$data->details,
                          'executionTime' => (String)$data->executionTime,
                          'rerunDate' => (String)$data->rerunDate,
                        );  
                }
            }
        }
        return $rerData;
    }
    
    public static function processXmlIntoObject($xmlRaw)
    {
        return simplexml_load_string($xmlRaw);
    }
    
    /**
     * Method to extract the XML by reid and parse it into SimpleXML Object
     * 
     * @param int $reid Run Execution ID
     * @return XMLObj The XML Object
     */
    public static function processTestResultsXml($reid, $replaceMasterRerun = false)
    {
        $xmlObj = self::processXmlIntoObject(self::loadXmlByReid($reid));
        
        $rerunData = reportsuitequeries::getAllRerunDataByParentReid($reid);
        if(!empty($rerunData))
        {
            $xmlObj = self::recalculateResults($reid, 
                    self::mergeRerunWithMaster($xmlObj, $rerunData, $replaceMasterRerun));
        }
        
        return $xmlObj;
    }
    
    /**
     * Method to extract the XML by reid and parse it into SimpleXML Object
     * 
     * @param int $reid Run Execution ID
     * @return XMLObj The XML Object
     */
    public static function processTestResultsXmlWithoutAdditMerge($reid, $replaceMasterRerun = false)
    {
        return self::processXmlIntoObject(self::loadXmlByReid($reid));
    }
    
    public static function mergeRerunWithMaster($xmlObj, $reRunData, $replaceMasterRerun)
    {
        //drupal_set_message('<pre>' . print_r($xmlObj, 1) . '</pre>');
       //drupal_set_message('<pre>' . print_r($reRunData, 1) . '</pre>');
        //Crazy loop cause i just dont care...good luck!
        $latestrreid = 0;
        $overrideScenarioRes = false;
       foreach($reRunData as $rrData)
       {
         $rreid = (String)$rrData['rreid']; 
        foreach($rrData['xmlObj']->testScenarios->TestScenario as $testScenario)
        {
           foreach($testScenario->testCases->TestCase as $testCase)
           {
              foreach($xmlObj->testScenarios->TestScenario as $m_testScenario)
              {
                if((String)$testScenario->name == (String)$m_testScenario->name)
                {
                  foreach ($m_testScenario->testCases->TestCase as $m_testCase)
                  {
                    if((String)$testCase->id == (String)$m_testCase->id)
                    {
                      //replace old results
                      $m_testCase->reRunData->$rreid->reid = $rrData['child_reid'];
                      $m_testCase->reRunData->$rreid->status = (String)$testCase->status;
                      $m_testCase->reRunData->$rreid->executionTime = (String)$testCase->executionTime;    
                      $m_testCase->reRunData->$rreid->comment = (String)$testCase->comment;   
                      $m_testCase->reRunData->$rreid->details = (String)$testCase->details;
                      $m_testCase->reRunData->$rreid->rerunDate = (String)$rrData['xmlObj']->runDateTimeStart;
                      if(isset($testCase->reservationNumber))
                          $m_testCase->reRunData->$rreid->reservationNumber = 
                             (String)$testCase->reservationNumber;   
                    }
                  }                      
                }
              }
           }
           $overrideScenarioRes = false;
        }
       }
       
       return $xmlObj;
    }
    
    public static function recalculateResults($reid, $xmlObj)
    {
        $t_passed = 0;
        $t_warning = 0;
        $t_failed = 0;
        foreach($xmlObj->testScenarios->TestScenario as $testScenario)
        {
            $s_passed = 0;
            $s_warning = 0;
            $s_failed = 0;
            foreach ($testScenario->testCases->TestCase as $testCase)
            {
               $rerData = self::gatherRerunOnCase($reid, $testCase);
               $key = isset($rerData['latest'])?'latest':'MASTER';
               switch ($rerData[$key]['status'])
               {
                   case 'Failed':$s_failed++;break;
                   case 'Passed':$s_passed++;break;
                   case 'Warning':$s_warning++;break;
               }
            }
            $testScenario->passed = $s_passed;
            $testScenario->failed = $s_failed;
            $testScenario->warning = $s_warning;
            $t_passed += $s_passed;
            $t_warning += $s_warning;
            $t_failed += $s_failed;
        }
        $xmlObj->passed = $t_passed;
        $xmlObj->warning = $t_warning;
        $xmlObj->failed = $t_failed;
        return $xmlObj;
    }
    
    
    public static function injectRerunPatchElementIntoXml($reid, $xmlObj)
    {
        $rerunData = reportsuitequeries::getAllRerunDataByParentReid($reid);
        if(!empty($rerunData))
        {
            $xmlObj = self::recalculateResults($reid, 
                    self::mergeRerunWithMaster($xmlObj, $rerunData, true));
        }
        
        return $xmlObj;
    }
    
    public static function processXmlIntoTestCasesResultData($xmlObj, $sortBy = null)
    {
        $testResultData = array();
        
        $testResultData['uuid'] = (String)$xmlObj['uuid'];
    
        foreach($xmlObj->testScenarios->TestScenario as $testScenario)
        {
            
            foreach($testScenario->testCases->TestCase as $testCase)
            {
                //change for override details
                $outcomeInfo = self::getOutcomeInfoByTestCase($testCase);
                //rerun
                $rerData = self::gatherRerunOnCase($testResultData['uuid'], $testCase);
                $key = isset($rerData['latest'])?'latest':'MASTER';
                $resvNumber = isset($rerData[$key]['reservationNumber'])?
                        $rerData[$key]['reservationNumber']:'';
                $outcomeStatus = $outcomeInfo['status'];
                //passoverrides everything
                if(isset($rerData['latest']['status']))
                $outcomeStatus = in_array($rerData['latest']['status'], array('Passed','Warning'))?
                        $rerData['latest']['status']:$outcomeInfo['status'];
                 
                $orgOutcome = array();
                if(isset($outcomeInfo['originalStatus']))
                    $orgOutcome = array('status' => $outcomeInfo['originalStatus'], 
                        'details' => $outcomeInfo['originalDetail']);
                
                $testResultData[(String)$testCase->id] =
                                array('outcome' => $outcomeStatus,
                                      'originalOutcome' => $orgOutcome,
                                      'duration' => $rerData[$key]['executionTime'],
                                      'description' => (String)$testCase->description,
                                      'reservationNumber' => $resvNumber,
                                      'failDets' => $outcomeInfo['detail'], 
                                      'scenario' => (String)$testScenario->name,
                                      'reportSuiteAddit' => (String)$testCase->reportSuitePatch,
                                      'resultExt' => (String)$testCase->ResultExt);
                
            }
        }
        
        //drupal_set_message('<pre>' . print_r($testResultData, 1) . '</pre>');
        if($sortBy === 'scenario')
            $testResultData = self::sortTestCasesResultDataByScenario($testResultData);
            
        return $testResultData;
    }
    
    private static function sortTestCasesResultDataByScenario($testResultData)
    {
        $testResultDataResort = array('uuid' => $testResultData['uuid']);
        
        foreach ($testResultData as $testCaseId => $data)
        {
           if($testCaseId !== 'uuid')
           $testResultDataResort[$data['scenario']][$testCaseId] = $data; 
        }
        return $testResultDataResort;
    }
    
    public static function processTestCaseIdInTestResults($reid)
    {
        $xmlObj = self::processTestResultsXml($reid);
        
        $testCases = array();
        
        foreach ($xmlObj->testScenarios->TestScenario as $scenario)
        {
            foreach($scenario->testCases->TestCase as $testCase)
            {
              $testCases[] = $testCase->id; 
            }
        }
        return $testCases;
    }

            
    public static function loadXmlByReid($reid)
    {
        db_set_active('taf');
        $query = db_select('xml_payload_storage', 'xps');
        $query->condition('xps.identifier_type', 'reid')
              ->condition('xps.identifier_id', $reid)
              ->fields('xps', array('result_xml_store'))
              ->range(0,1);
        $result = $query->execute()->fetchField();
        db_set_active();
        
        
        return $result;
    }
    
    public static function updateXmlByReid($reid, $xml)
    {
        db_set_active('taf');
        $query = db_update('xml_payload_storage')
                 ->fields(array(
                  'result_xml_store' => $xml,   
                 ))
                ->condition('identifier_type', 'reid', '=')
                ->condition('identifier_id', $reid, '=')
                ->execute();
        db_set_active();
        return;
    }
    
    
    public static function editTestCaseOutcome($dataArr)
    {
        //drupal_set_message('<pre>'.  print_r($dataArr, 1).'</pre>');
        $xmlObj = reportsuiteXmlProcessor::processTestResultsXmlWithoutAdditMerge($dataArr['testCaseInfo']['reid']);
        $testScenarioNo = 0;
        $tcFound = false;
        //drupal_set_message('<pre>'.  print_r($xmlObj, 1).'</pre>');
        foreach ($xmlObj->testScenarios->TestScenario as $testScenario)
        {
            if((String)$testScenario->name == $dataArr['testCaseInfo']['details']['scenario'])
            {
                $testCaseNo = 0;
                foreach ($testScenario->testCases->TestCase as $testCase)
                {
                    if ((String)$testCase->id == $dataArr['testCaseInfo']['testCaseId'])
                    {
                        //drupal_set_message('<pre>'.print_r($testCase, 1).'</pre>');
                        $testCase->reportSuiteAddit->overrideTcOutcomeFlag = TRUE;
                        $testCase->reportSuiteAddit->overrideTcOutcomeValue = 
                                 $dataArr['changedOutcomeValue'];
                        $testCase->reportSuiteAddit->overrideTcOutcomeReason = 
                                 $dataArr['reason'];
                        $tcFound = true;
                        break;
                    }
                    $testCaseNo++;
                }
                
                if($tcFound)
                {
                    $resArr = self::getOverallResultsFromTestCasesInScenario($testScenario);
                    $testScenario->reportSuiteAddit->outcomeOverrideFlag = TRUE;
                    $testScenario->reportSuiteAddit->passed = $resArr['Passed'];
                    $testScenario->reportSuiteAddit->warning = $resArr['Warning'];
                    $testScenario->reportSuiteAddit->failed = $resArr['Failed'];
                    $testScenario->reportSuiteAddit->tafError = $resArr['TAFError'];
                }
                break;
            }
            $testScenarioNo++;
        }
        
        if ($tcFound)
        {
            $resSuiteArr = self::getOverallResultsFromScenariosInSuite($xmlObj);
            $xmlObj->reportSuiteAddit->outcomeOverrideFlag = TRUE;
            $xmlObj->reportSuiteAddit->passed = $resSuiteArr['Passed'];
            $xmlObj->reportSuiteAddit->warning = $resSuiteArr['Warning'];
            $xmlObj->reportSuiteAddit->failed = $resSuiteArr['Failed'];
            $xmlObj->reportSuiteAddit->tafError = $resSuiteArr['TAFError'];
        }
        self::updateXmlByReid($dataArr['testCaseInfo']['reid'], $xmlObj->asXML());
    }
    
    public static function setFailureReason($reid, $dataArr)
    {
        $xmlObj = self::processTestResultsXmlWithoutAdditMerge($reid);
        
        if(isset($dataArr['scenario']) && isset($dataArr['testCaseId']))
        {
            foreach ($xmlObj->testScenarios->TestScenario as $testScenario)
            {
                if((String)$testScenario->name === $dataArr['scenario'])
                {
                    foreach ($testScenario->testCases->TestCase as $testCase)
                    {
                        if ((String)$testCase->id === $dataArr['testCaseId'])
                        {
                            $testCase->ResultExt->FailureReason->PointOfIssue = 
                                $dataArr['failureReason'];
                            $testCase->ResultExt->FailureReason->Observsation = 
                                $dataArr['failureReasonComment'];
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            $xmlObj->reportSuiteAddit->ResultExt->FailureReason->PointOfIssue = 
                    $dataArr['failureReason'];
            $xmlObj->reportSuiteAddit->ResultExt->FailureReason->Observsation = 
                    $dataArr['failureReasonComment'];
        }
        self::updateXmlByReid($reid, $xmlObj->asXML());
    }
    
    public static function getFailureReason($reid, $byParam = array())
    {
        $xmlObj = self::processTestResultsXml($reid);
        $frFound = false;
        $frData = array();
        if(!empty($byParam))
        {
            foreach ($xmlObj->testScenarios->TestScenario as $testScenario)
            {
                if ((String)$testScenario->name == $byParam['scenario'])
                {
                    foreach ($testScenario->testCases->TestCase as $testCase)
                    {
                        if ((String)$testCase->id == $byParam['testCaseId'])
                        {
                            if(isset($testCase->ResultExt->FailureReason))
                            {
                            $frData['failureReason'] = $testCase->ResultExt->
                                        FailureReason->PointOfIssue;
                            $frData['failureReasonComment'] = $testCase->ResultExt->
                                        FailureReason->Observsation;
                            $frFound = true;
                            break;
                            }
                        }
                    }
                }
            }
        }
        else
        {
            if(isset($xmlObj->reportSuiteAddit->ResultExt->FailureReason))
            {
                 $frData['failureReason'] = $xmlObj->reportSuiteAddit->ResultExt->
                         FailureReason->PointOfIssue;
                 $frData['failureReasonComment'] = $xmlObj->reportSuiteAddit->ResultExt->
                         FailureReason->Observsation;
                $frFound = true;
            }
        }
        
        if($frFound)
            return $frData;
        else
            return false;
    }
    
    public static function getParentElementFromXmlObject($pElement, $xmlObj)
    {
        
    }
    
    public static function unsetFailureReason()
    {
        
    }
    
    public static function coverageMatrixProcessor($testCases)
    {
        $coverageDetails = array();
        foreach ($testCases as $testCase)
        {
            $coverageDetails[] = reportsuite::parseCoverageOnTestCaseId($testCase);
        }
        //drupal_set_message('<pre>'.print_r($coverageDetails, 1).'</pre>');
        return $coverageDetails;
    }
    
    public static function coverageAlias($aliasKey)
    {
        $coverageAlias = array(
            'default' => array('name' => 'Not Available', 
                                         'type' => 'N/A',
                                         'desc' => ''),
             'ADT' => array('name' => 'Adult', 'type' => 'PAX Type', 'desc' => ''),
             'CNN' => array('name' => 'Child', 'type' => 'PAX Type', 'desc' => ''),
             'CHD' => array('name' => 'Child', 'type' => 'PAX Type', 'desc' => ''),
             'INF' => array('name' => 'Infant', 'type' => 'PAX Type', 'desc' => ''),
             'INFST' => array('name' => 'Infant on Seat', 'type' => 'PAX Type', 'desc' => ''),
             'UNN' => array('name' => 'Unaccompanied Minor Passenger', 'type' => 'PAX Type', 'desc' => ''),
             'UNMR' => array('name' => 'Unaccompanied Minor Passenger', 'type' => 'PAX Type', 'desc' => ''),
             'CMP' => array('name' => 'Accompanied Minor Passenger', 'type' => 'PAX Type', 'desc' => ''),
             'OW' => array('name' => 'One Way', 'type' => 'Flight Type', 'desc' => ''),
             'RT' => array('name' => 'Return', 'type' => 'Flight Type', 'desc' => ''),
             'MC' => array('name' => 'MultiCity', 'type' => 'Flight Type', 'desc' => ''),
             'BS' => array('name' => 'Business Class', 'type' => 'Search Type', 'desc' => ''),
             'BSNS' => array('name' => 'Business Class', 'type' => 'Search Type', 'desc' => ''),
             'BUSINESS' => array('name' => 'Business Class', 'type' => 'Search Type', 'desc' => ''),
             'NONFLEX' => array('name' => 'non-Flexible Dates', 'type' => 'Search Type', 'desc' => ''),
             'SPECDATE' => array('name' => 'Specified Dates', 'type' => 'Search Type', 'desc' => ''),
             'DIRECTFLT' => array('name' => 'Direct Flights', 'type' => 'Search Type', 'desc' => ''),
             'FLEX' => array('name' => 'Flexible Dates', 'type' => 'Search Type', 'desc' => ''),
             'CAR' => array('name' => 'Car', 'type' => 'Ancillaries', 'desc' => ''),
             'Car' => array('name' => 'Car', 'type' => 'Ancillaries', 'desc' => ''),
             'HTL' => array('name' => 'Hotel', 'type' => 'Ancillaries', 'desc' => ''),
             'Hotel' => array('name' => 'Hotel', 'type' => 'Ancillaries', 'desc' => ''),
             'INS' => array('name' => 'Insurance', 'type' => 'Ancillaries', 'desc' => ''),
             'SEATS' => array('name' => 'Seat Select', 'type' => 'Ancillaries', 'desc' => ''),
             'Wifi' => array('name' => 'in-Flight Wifi', 'type' => 'Ancillaries', 'desc' => ''),
             'Insurance' => array('name' => 'Insurance', 'type' => 'Ancillaries', 'desc' => ''),
             'DONATION' => array('name' => 'Donation', 'type' => 'Ancillaries', 'desc' => ''),
             'FUELCARD' => array('name' => 'Fuel Card Coupon', 'type' => 'Ancillaries', 'desc' => ''),
             'SS' => array('name' => 'Super Shuttle Transportation', 'type' => 'Ancillaries', 'desc' => ''),
             'VISA' => array('name' => 'VISA', 'type' => 'Payment Type', 'desc' => ''),
             'MCARD' => array('name' => 'MasterCard', 'type' => 'Payment Type', 'desc' => ''),
             'MASTER' => array('name' => 'MasterCard', 'type' => 'Payment Type', 'desc' => ''),
             'MASTERCARD' => array('name' => 'MasterCard', 'type' => 'Payment Type', 'desc' => ''),
             'VISA3D' => array('name' => 'VISA 3D Secure', 'type' => 'Payment Type', 'desc' => ''),
             'MASTER3D' => array('name' => 'MasterCard 3D Secure', 'type' => 'Payment Type', 'desc' => ''),
             'AMEX' => array('name' => 'American Express', 'type' => 'Payment Type', 'desc' => ''),
             'DINERS' => array('name' => 'Diners Club', 'type' => 'Payment Type', 'desc' => ''),
             'WSUN' => array('name' => 'Western Union', 'type' => 'Payment Type', 'desc' => ''),
             'DISCOVER' => array('name' => 'Discover', 'type' => 'Payment Type', 'desc' => ''),
             'UATP' => array('name' => 'UATP', 'type' => 'Payment Type', 'desc' => ''),
             'BML' => array('name' => 'Bill Me Later', 'type' => 'Payment Type', 'desc' => ''),
             'GIFT' => array('name' => 'Gift Card', 'type' => 'Payment Type', 'desc' => ''),
             'JCB' => array('name' => 'Japan Credit Bureau', 'type' => 'Payment Type', 'desc' => ''),
             'BCA' => array('name' => 'Bank Central Asia (BCA)', 'type' => 'Payment Type', 'desc' => ''),
             'MANDIRI' => array('name' => 'Bank Mandiri', 'type' => 'Payment Type', 'desc' => ''),
             'CARTVERIFY' => array('name' => 'Cart Verification', 'type' => 'Basic Features', 'desc' => 'Verify Cart on Flow'),
             'LOGIN' => array('name' => 'Account/Profiles', 'type' => 'Basic Features', 'desc' => 'TDP User Account System'),
             
             //Temp for regression Test Case ID
             'Localization' => array('name' => 'Localization', 'type' => 'Basic Features', 'desc' => 'Locale Selected'),
        );
        
        if (!isset($coverageAlias[$aliasKey]))
        {
            $aliasKey = 'default';
        }
        
        return $coverageAlias[$aliasKey];
    }
    
    
    public static function getMailingList($listType, $section)
    {
        $file = drupal_get_path('module', 'reportsuite').'/lib/reportMaillingList.xml';
        $xmlObj = simplexml_load_file($file);
        $listFound = false;
        $list = array();
        foreach ($xmlObj as $k => $v)
        {
            if($k === $listType)
            {
                $elAttr = $v->attributes();
                if($elAttr == $section)
                {
                    $listFound = true;
                    foreach($v as $k2 => $v2)
                    {
                        $list[] = array(
                            'projectid' => (int)$v2->attributes()->project,
                            'email' => (String)$v2->attributes()->email
                        );
                    }
                    break;
                }
            }
        }
        if($listFound)
            return $list;
        else
            return drupal_set_message('LIST NOT FOUND' , 'error');   
    }
    
    public static function setMailingList()
    {
        
    }
    
}

?>
