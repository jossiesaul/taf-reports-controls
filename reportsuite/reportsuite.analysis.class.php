<?php

/*
 * To change this template, choose Tools | Templates        
 * and open the template in the editor.
 */

class reportsuiteanalysis
{
    
    public static function getNumberOfTestCasesOnProject($tpid)
    {
        $numOfTestCases = 0;
        foreach(reportsuitequeries::getTestCasesByTpid($tpid) as $tc)
        {
            $numOfTestCases++;
        }
        return $numOfTestCases;
    }
    
    public static function getNumberOfActiveTestCasesOnProject($tpid, $dept)
    {
        $numOfTestCases = 0;
        $testCases = reportsuiteXmlProcessor::processTestCaseIdInTestResults(
                                 reportsuitequeries::getLastReidByTpidAndDept($tpid, $dept));
        
        foreach($testCases as $testCase)
        {
            $numOfTestCases++;
        }
        return $numOfTestCases;
    }
    
    public static function processTestCaseGrowthDataFromListOfTestCases($listOfTestCases)
    {
        $growthByDate = array();
        foreach($listOfTestCases as $tc){
            
            $date = date('Y-m-d', strtotime($tc['created_at']));
            if(isset($growthByDate[$date]))
                $growthByDate[$date]++;
            else
                $growthByDate[$date] = 1;
        }
        return $growthByDate;
    }
    
    public static function getGrowthOfTestCasesOnProject($tpid)
    {
        $growthByDate = array();
        foreach(reportsuitequeries::getTestCasesByTpid($tpid) as $testCase)
        {
            $date = date('Y-m-d', strtotime($testCase->created_at));
            if(isset($growthByDate[$date]))
                $growthByDate[$date]++;
                    else
                        $growthByDate[$date] = 1;
        }
        return $growthByDate;
    }
    
    public static function getGrowthOfTestCasesOnProjectReleasePhase(
            $tpid, $releasePhasesAndDates)
    {
        $growthByReleasePhase = array();
        $datesToQuery = array();
        $dateFrom = '';
        $dateTo = '';
        $prevRelease;
        $firstDateNotParse;
        $last = count($releasePhasesAndDates);
        $current = 1;
        foreach($releasePhasesAndDates as $testcycle => $releasePhasesAndDates)
        {
            //Get the time span between the start of the release
            //phase until the next start of the release phase
            if(isset($prevRelease))
            {
                $datesToQuery[$prevRelease]['to'] = 
                    date('Y-m-d', strtotime('-1 day',
                    strtotime($releasePhasesAndDates)));
            }
            
            $datesToQuery[$testcycle]['from'] = $releasePhasesAndDates;
            
            //For next in line
            $prevRelease = $testcycle;
            
            //if its is the last in the queue then do todays date
            if($current == $last)
            {
                $datesToQuery[$testcycle]['to'] = 
                    date('Y-m-d');
            }
            $current++;
        }
        
        foreach($datesToQuery as $testcycle => $dates)
        {
            foreach(reportsuitequeries::getTestCasesByDate($tpid, $dates) 
                    as $result)
            {
                if(isset($growthByReleasePhase[$testcycle]))
                    $growthByReleasePhase[$testcycle]++;
                    else
                        $growthByReleasePhase[$testcycle] = 1;
                    
            }
        }
        
        return $growthByReleasePhase;
    }
    
    
    /**
     * method to get the last pass rate on a project and department
     * 
     * @param Int $tpid
     * @param String $dept
     * @return Mixed Percentage
     */
    public static function getLastPassRate($tpid, $dept)
    {
        $autoResultStore = reportsuitequeries::getRunExecutionResultsSummaryByReid(
                                           reportsuitequeries::getLastReidByTpidAndDept($tpid, $dept));
       
        return reportsuitequeries::calculatePassRate($autoResultStore['Total'], 
                                                    $autoResultStore['Pass']+
                                                    $autoResultStore['Warning'],
                                                    true);
    }
    
    public static function getAveragePassRate($tpid, $dept)
    {
        $listOfReid = reportsuitequeries::getAllReidByTpidAndDept($tpid, $dept);
        $totalAutoResultStore = array('Total' => 0, 'PassWarning' => 0);
        foreach ($listOfReid as $reid)
        {
                $autoResultStore = reportsuitequeries::getRunExecutionResultsSummaryByReid($reid->reid);
                $totalAutoResultStore['Total'] += $autoResultStore['Total'];
                $totalAutoResultStore['PassWarning'] += 
                        $autoResultStore['Pass']+$autoResultStore['Warning'];
        }
        
         return reportsuitequeries::calculatePassRate($totalAutoResultStore['Total'],
                                                      $totalAutoResultStore['PassWarning'],
                                                      true);
    }
    
    /**
     * 
     * @param type $reid
     * 
     */
    public static function runResultAnalysisRawData($reid, $xmlObj, $testCasesData)
    {
        $analysisRawData = array();
        
        $testCases = reportsuiteXmlProcessor::processTestCaseIdInTestResults($reid);
        $coverageDetails = reportsuiteXmlProcessor::coverageMatrixProcessor($testCases);
        $execListData = reportsuite::processExecutionListData(
                                 reportsuitequeries::getRunExecutionList($reid));
        
        //drupal_set_message('<pre>' . print_r($testCasesData, 1) . '</pre>');
         foreach($coverageDetails as $coverage)
         {
             //drupal_set_message('<pre>'.  print_r($coverage,1).'</pre>');
             $testCaseId = (String)$coverage['testCaseID'];
             $scenario = $testCasesData[$testCaseId]['scenario'];
             
             if(isset($execListData[$scenario][$testCaseId]))
                 $stepsDetails =self::processExecutionRunStepsAnalysis(
                            $testCaseId,
                            $execListData[$scenario][$testCaseId],
                            $testCasesData[$testCaseId],
                            $xmlObj->runDateTimeStart
                            );
             else
                 $stepsDetails = 'No Data';
             
             $analysisRawData[$scenario] [$testCaseId]= array(
                    //'coverageDetails' => $coverage['details'],
                    'outcome' => $testCasesData[$testCaseId]['outcome'],
                    'duration' => $testCasesData[$testCaseId]['duration'],
                    'failDetail' => $testCasesData[$testCaseId]['failDets'],
                    'stepsDetails' => $stepsDetails,
             );  
         }
         return $analysisRawData;
    }
    
    public static function processExecutionRunStepsAnalysis($testcaseId, $execListData, 
            $testCasesData, $runDate)
    {
        //Go through each cases steps
        //drupal_set_message('<pre>' . print_r($execListData, 1) . '</pre>');
        $tcTestData = array();
        
        if(empty($execListData))           
            return $tcTestData;
        
        //Number of Steps
        $tcTestData['testActionStepDetails'] = array(
            'totalNumOfSteps' => reportsuiteELDProcessor::getNumberOfTotalSteps($execListData),
            'numOfStepsPassed'  => reportsuiteELDProcessor::getNumberOfPassingSteps($execListData, $testCasesData),
            'numOfStepsFailed' => reportsuiteELDProcessor::getNumberFailingSteps($execListData, $testCasesData),
            'numOfStepsWarning' => reportsuiteELDProcessor::getNumberWarningSteps($testCasesData),
            'failingPointStep' => reportsuiteELDProcessor::getfailingPointStep($execListData, $testCasesData),
            );
        
        //Get Routes
        $tcTestData['routes'] = reportsuiteELDProcessor::getRoutes($execListData);
        //get search types
        $tcTestData['searchType'] = reportsuiteELDProcessor::getSearchType($execListData);
        //Get Flows 
        $tcTestData['testFlow'] = reportsuiteELDProcessor::getPageFlows($execListData, $testCasesData);
        //get Payment Type
        $tcTestData['paymentType'] = reportsuiteELDProcessor::getPaymentType($execListData);
        //Get Pax Selection
        $tcTestData['paxSelection'] = reportsuiteELDProcessor::getPaxSelectionData($execListData);
        //Get Input dates
        $tcTestData['flightDates'] = reportsuiteELDProcessor::getInputDates($execListData, $runDate);
        //Get Input dates
        $tcTestData['ancillaries'] = reportsuiteELDProcessor::getAncillaries($execListData);
        
        //Addon here but class::methods must be created too.
        
        return $tcTestData;
    }

    public static  function processAnalysisDataToStatisticalOutput($analysisRawData)
    {
        $statisticalArrayVariables = array(
                'duration' => array(),
                'paymentType' => array(),
                'searchType' => array(),
                'actionStepsStats' => array(
                    'totalNumOfSteps' => 0, 'numOfStepsPassed' => 0, 
                    'numOfStepsFailed' => 0, 'numOfStepsWarning' => 0),
                'failureCommonality' => array(),
                'pointOfFailureAS' => array(),
                'routesUsage' => array(),
                'pageFlow' => array(),
                'ancillaries' => array(),
            );
        
        $statisticalData = array( 
            'total' => $statisticalArrayVariables,
        );
        //by test case
        //Count number Flight Type fail,pass,warning
        //drupal_set_message('<pre>' . print_r($analysisRawData, 1) . '</pre>');
        
        foreach ($analysisRawData as $tScenario => $testCaseData)
        {
            $statisticalData[$tScenario] = $statisticalArrayVariables;
            foreach($testCaseData as $tCaseId => $dt)
            {
                /**
                 * TOTAL
                 */
                $statisticalData['total']['duration'] = 
                        self::roundOffCalculationRunDurationHighLowMean(
                                $dt['duration'], $statisticalData['total']['duration']);
                $statisticalData['total']['paymentType'] =
                        self::roundOffStepDetailsByType($dt, 'paymentType', $statisticalData['total']['paymentType']);
                $statisticalData['total']['searchType'] =
                        self::roundOffStepDetailsByType($dt, 'searchType', $statisticalData['total']['searchType']);
                $statisticalData['total']['failureCommonality'] =
                        self::roundOffFailureDetailCommonality(
                                $dt['failDetail'], $tCaseId, $statisticalData['total']['failureCommonality']);
                
                if($dt['stepsDetails'] != 'No Data'){
                $statisticalData['total']['actionStepsStats'] =
                        self::roundOffNumOfActionSteps(
                                $dt['stepsDetails']['testActionStepDetails'], $statisticalData['total']['actionStepsStats']);
                $statisticalData['total']['routesUsage'] = 
                        self::roundOffRoutesUsage(
                                $dt['stepsDetails']['routes'], $dt['outcome'], 
                                $tCaseId, $statisticalData['total']['routesUsage']);
                
                $statisticalData['total']['pageFlow'] = 
                        self::roundOffTestFlow(
                                $dt['stepsDetails']['testFlow'], $tCaseId, 
                                $statisticalData['total']['pageFlow']);
                
                $statisticalData['total']['pointOfFailureAS'] = 
                        self::roundOffPofActionStep(
                                $dt['stepsDetails']['testActionStepDetails'], 
                                $tCaseId,
                                $statisticalData['total']['pointOfFailureAS']);
                
                $statisticalData['total']['ancillaries'] = 
                        self::roundOffAncilliaries(
                                $dt['stepsDetails']['ancillaries'], $dt['outcome'], 
                                $tCaseId, $statisticalData['total']['ancillaries']);
                }
                
                /**
                 * Per Scenario
                 */
                $statisticalData[$tScenario]['duration'] = 
                        self::roundOffCalculationRunDurationHighLowMean(
                                $dt['duration'], $statisticalData[$tScenario]['duration']);
                $statisticalData[$tScenario]['paymentType'] =
                        self::roundOffStepDetailsByType($dt, 'paymentType', $statisticalData[$tScenario]['paymentType']);
                $statisticalData[$tScenario]['searchType'] =
                        self::roundOffStepDetailsByType($dt, 'searchType', $statisticalData[$tScenario]['searchType']);
                $statisticalData[$tScenario]['failureCommonality'] =
                        self::roundOffFailureDetailCommonality(
                                $dt['failDetail'], $tCaseId, $statisticalData[$tScenario]['failureCommonality']);
                
                if($dt['stepsDetails'] != 'No Data'){
                $statisticalData[$tScenario]['actionStepsStats'] =
                        self::roundOffNumOfActionSteps(
                                $dt['stepsDetails']['testActionStepDetails'], $statisticalData[$tScenario]['actionStepsStats']);
                $statisticalData[$tScenario]['routesUsage'] = 
                        self::roundOffRoutesUsage(
                                $dt['stepsDetails']['routes'], $dt['outcome'], 
                                $tCaseId, $statisticalData[$tScenario]['routesUsage']);
                $statisticalData[$tScenario]['pageFlow'] = 
                        self::roundOffTestFlow(
                                $dt['stepsDetails']['testFlow'], $tCaseId, 
                                $statisticalData[$tScenario]['pageFlow']);
                $statisticalData[$tScenario]['pointOfFailureAS'] = 
                        self::roundOffPofActionStep(
                                $dt['stepsDetails']['testActionStepDetails'], 
                                $tCaseId,
                                $statisticalData[$tScenario]['pointOfFailureAS']);
                $statisticalData[$tScenario]['ancillaries'] = 
                        self::roundOffAncilliaries(
                                $dt['stepsDetails']['ancillaries'], $dt['outcome'], 
                                $tCaseId, $statisticalData[$tScenario]['ancillaries']);
                }
            }
        }
      //drupal_set_message('<pre>' . print_r($statisticalData, 1) . '</pre>');
       return $statisticalData;
    }
    
    protected static function roundOffAncilliaries($ancillaries, $status, $tcid, $addOn)
    {
      if(!is_array($ancillaries)) return $addOn;
      
        foreach ($ancillaries as $dt)
        {
            if(empty($addOn[$dt]['TotalTestCases']))
                $addOn[$dt]['TotalTestCases'] = 1;
           else
                $addOn[$dt]['TotalTestCases']++;
            
            if(empty($addOn[$dt][$status])){
                $addOn[$dt][$status] = array('counter' => 1, 'testCases' => array($tcid));
            }else{
                $addOn[$dt][$status]['counter']++;
                array_push($addOn[$dt][$status]['testCases'], $tcid);
            }
        }
        return $addOn;
    }

    /**
     * pof -> Point of Failure
     * 
     * @param type $actionStep
     */
    protected static function roundOffPofActionStep($actionStep, $tcid, $addOn)
    {
        $newArr = false;
        $exist = false;
        $i = 0;
        if(empty($actionStep['failingPointStep']) || !is_array($actionStep['failingPointStep'])) 
            return $addOn;
        foreach($actionStep['failingPointStep'] as $asd)
        {
            $actionStepStat = array(
                            //'status' => $asd['status'],
                            'actionStepDetail' => $asd['actionStepDetail'],
                            'counter' => 1,
                            'testCases' => array($tcid)
                        );
            if(empty($addOn))
            {
                $addOn[0] = $actionStepStat;
            }
            else
            {
                    foreach($addOn as $dt)
                    {
                        if(!isset($addOn[$i])) continue;
                        if(count(array_intersect($dt['actionStepDetail'], $asd['actionStepDetail'])) 
                            == count($asd['actionStepDetail']))
                                //&& $asd['status'] == $addOn[$i]['status'])
                        {
                            $addOn[$i]['counter']++;
                            $newArr = false;
                            $exist = true;
                            array_push($addOn[$i]['testCases'], $tcid);
                        }
                        else
                        {
                            if(!$exist){
                                $newArr = true;
                            }
                        }
                        $i++;
                    }
                 if($newArr)
                     $addOn[$i] = $actionStepStat;
            }
        }
        return $addOn;
    }

    protected static function roundOffTestFlow($testFlow, $tcId, $addOn)
    {
        $testFlowStats = array(
                    'numOfFlow'  =>  count($testFlow),
                    'counter' => 1,
                    'testCases' => array($tcId),
            );
        $newArr = false;
        $exist = false;
        $i = 0;
        if(empty($addOn))
        {
            $addOn['nofStats'][0] = $testFlowStats;
            $addOn['pageStats'] = array();
        }
        else
        {
            foreach($addOn['nofStats'] as $dt)
            {
                if(!isset($dt['numOfFlow'])) continue;
                if($dt['numOfFlow'] == count($testFlow))
                {
                    $addOn['nofStats'][$i]['counter']++;
                    array_push($addOn['nofStats'][$i]['testCases'], $tcId);
                    $newArr = false;
                    $exist = true;
                }
                else
                {
                    if(!$exist){
                        $newArr = true;
                    }
                }
                $i++;
            }
             if($newArr)
                $addOn['nofStats'][$i] = $testFlowStats;
        }
        $addOn['pageStats'] = self::roundOffTestFlowStage($testFlow, $addOn['pageStats']);
        return $addOn;
    }
    private static function roundOffTestFlowStage($testFlow, $tfAddOn)
    {
        if(!is_array($testFlow)) return $tfAddOn;
        foreach ($testFlow as $flow)
        {
            switch($flow['status'])
            {
                case 'Passed':
                    if(!isset($tfAddOn[$flow['checkpointId']]['Passed']))
                       $tfAddOn[$flow['checkpointId']]['Passed'] = 1;
                    else
                        $tfAddOn[$flow['checkpointId']]['Passed']++;
                    break;
                case 'Warning':
                    if(!isset($tfAddOn[$flow['checkpointId']]['Warning']))
                       $tfAddOn[$flow['checkpointId']]['Warning'] = 1;
                    else
                        $tfAddOn[$flow['checkpointId']]['Warning']++;
                    break;
                case 'Failed':
                    if(!isset($tfAddOn[$flow['checkpointId']]['Failed']))
                       $tfAddOn[$flow['checkpointId']]['Failed'] = 1;
                    else
                        $tfAddOn[$flow['checkpointId']]['Failed']++;
                    break;
                default:
                    if(!isset($tfAddOn[$flow['checkpointId']]['NA']))
                       $tfAddOn[$flow['checkpointId']]['NA'] = 1;
                    else
                        $tfAddOn[$flow['checkpointId']]['NA']++;
                    break;
            }
        }
        return $tfAddOn;
    }

    protected static function roundOffRoutesUsage($routes, $status, $tcId, $addOn)
    {
        $newArr = false;
        $exist = false;
        $i = 0;
        $routeUsage = array('routes' => $routes, 'status' => $status,
                'testCases' => array($tcId), 'counter' => 1);
        if(empty($addOn)){
            $addOn[0] = $routeUsage;
        }else{
            foreach($addOn as $dt)
            {
                if(!is_array($routes)) continue;
                //matches arrays first then count of equal to array route queried
                if(count(array_intersect($dt['routes'], $routes)) == count($routes) &&
                        $dt['status'] == $status)
                {
                    $addOn[$i]['counter']++;
                    array_push($addOn[$i]['testCases'], $tcId);
                    $newArr = false;
                    $exist = true;
                }
                else
                {
                    if(!$exist){
                        $newArr = true;
                    }
                }
                $i++;
            }
            if($newArr)
                $addOn[$i] = $routeUsage;
        }
        return $addOn;
    }
    
    protected static function roundOffFailureDetailCommonality($failureDetail, $tcId, $addOn)
    {
        $errorMesg = reportsuite::splitErrorMessages($failureDetail);
        $addedInCounter = array();
        if(empty($errorMesg)) return $addOn;
        
        for($ei=1; $ei <= count($errorMesg); $ei++)
        {
            $newError = false;
            if(!isset($errorMesg[$ei])) continue;
            $errMsg = trim($errorMesg[$ei]);
            if(empty($addOn))
            {
                $addOn[0]['errMsg'] = $errMsg;
                $addOn[0]['counter'] = 1;
                $addOn[0]['testCases'] = array($tcId);
            }
            else
            {
                for($ai=0; $ai <= count($addOn); $ai++)
                {
                    if(isset($addOn[$ai]) && $addOn[$ai]['errMsg'] == $errMsg)
                    {
                       $addedInCounter[] = $errMsg;
                       $addOn[$ai]['counter']++;
                       
                       $ti = $ai+1;
                       if(!in_array($tcId, $addOn[$ai]['testCases']))
                            array_push($addOn[$ai]['testCases'], $tcId);
                    }
                    else
                    { 
                       $newError=true;
                    }  
                }
                
                if($newError && !in_array($errMsg, $addedInCounter))
                       $addOn[count ($addOn)] = array(
                            'errMsg' => $errMsg,
                            'counter' => 1,
                            'testCases' => array($tcId),
                        );
            }
        }
       return $addOn;
    }
    
    protected static function roundOffNumOfActionSteps($actionStepDetails, $addon)
    {
        if(!is_array($actionStepDetails)) return $addon;
        foreach($actionStepDetails as $k=>$v)
        {
            if(!is_array($v))
            $addon[$k] = $addon[$k] == 0?$v:$addon[$k]+$v;
        }
        return $addon;
    }


    protected static function roundOffCalculationRunDurationHighLowMean($duration, $addon)
    {
        $nRun = array('loopCounter' => 1);
        $durInSec = reportsuite::parseHHMMSSDurationIntoTotalSeconds($duration);
        
        if(!empty($addon))
        {
            $addon['loopCounter']++;
            if($addon['low'] > $durInSec) $addon['low'] = $durInSec;
            if($addon['high'] < $durInSec) $addon['high'] = $durInSec;
            $addon['total'] = $addon['total']+$durInSec;
            $addon['mean'] = $addon['total']/$addon['loopCounter'];
        }
        else
        {
            $nRun['low'] = $durInSec;
            $nRun['mean'] = $durInSec;
            $nRun['high'] = $durInSec;
            $nRun['total'] = $durInSec;
            $addon = $nRun;
        }
        return $addon;
    }
    
    /**
     * Resusable Method to roundOff a particular element with non array value
     * 
     * Use Type
     * 
     * @param type $testCaseDt
     * * @param type $type
     * @param type $addon
     */
    private static function roundOffStepDetailsByType($testCaseDt, $type, $addon)
    {
        if(!isset($testCaseDt['stepsDetails'][$type]) ||
                $testCaseDt['stepsDetails'] == 'No Data') return '';
        $sType = $testCaseDt['stepsDetails'][$type];
         if(!empty($addon[$sType][$testCaseDt['outcome']]))
        {
             $addon[$sType][$testCaseDt['outcome']]++;
        }
        else
        {
            if(!empty($sType))
            {
                $addon[$sType][$testCaseDt['outcome']] = 1;
            }
        }
         return $addon;
    }

    public static function calculateTimeSaved($strDur, $gridDur)
    {
        
    }
    
    /*
     * Different to the method runResultAnalysisRawData <br />
     * This method will process the data and provide numbers in simple format
     * @param type $reid
     */
    public static function runResultAnalysisData($reid)
    {
            
    }
    
    /**
     * 
     * Use the Test Cases ID to provide a broader Coverage Detail
     * 
     * @param type $tpid
     */
    public static function processCoverageAnalysisOnProject($tpid)
    {
        $testCaseCoverageMatrix = array();
        
        #@TODO: add patch to merge with executionStepData for Coverage Matrix on other cases
        //That does not have cases
        foreach (reportsuitequeries::getTestCasesByTpid($tpid) as $resultSet) 
        {
            //drupal_set_message('<pre>' . print_r($resultSet, 1) . '</pre>');
            $testCaseCoverageMatrix[(String)$resultSet->scenario]
                                   [(String)$resultSet->testcase_id] = 
                                   reportsuite::parseCoverageOnTestCaseId(
                                           (String)$resultSet->testcase_id);
        }
        return self::simplifyCoverageMatrixDetail($testCaseCoverageMatrix);
    }
    
    public static function simplifyCoverageMatrixDetail($projectCoverageData)
    {
        $coverageRebundled = array();
        foreach($projectCoverageData as $scenario => $testCasesCoverage)
        {
            foreach ($testCasesCoverage as $testCase => $coverage)
            {
                foreach($coverage['details']['coverage'] as $coverType => $coverCase)
                {
                    if(!empty($coverCase))
                    {
                        foreach($coverCase as $k => $v)
                        {
                            $coverageRebundled[$scenario][$testCase][$coverType][] = 
                            $v['name'];
                        }
                    }
                }
            }
        }
        return $coverageRebundled;
    }


    /**
     * 
     * This Method gets all Run, Passing, Warnings, and Fails Test Cases
     * 
     * and Compiles/Gather Information of the Run Execution too
     * 
     * @param type $tpid
     * @param type $dept
     * @param type $timespan
     */
    protected static function grabAllTestCasesResultData($tpid, $dept, $additData = array())
    {
        
       if(isset($additData['timespan']))
       {
           $reidStore = reportsuitequeries::getAllReidByTpidAndDeptOnTimeSpan(
                            $tpid, $dept, $additData['timespan']);
       }
       elseif($additData['reidCollection'])
       {
           $reidStore = reportsuitequeries::getReidByCollection($additData['reidCollection']);
       }
       else
       {
           $reidStore = reportsuitequeries::getAllReidByTpidAndDept($tpid, $dept);
       }
       
        $reidList = array();
        //Variable to adjust the AutomationResultStore cache
        $reAdditionalDets = array();
        $testCycleLastKnown = 'MR';
        $releaseStageLastKnown = 'NA';
        foreach ($reidStore as $reData)
        {
            
            //This if and else part will allow all non tagged run to continue
            //off where they left off
            //Getting the last known test cycle
            if(!empty($reData->test_cycle))
            {
                if($testCycleLastKnown != $reData->test_cycle)
                {
                   $testCycleLastKnown = $reData->test_cycle;
                }
                else
                {
                   $testCycleLastKnown = $reData->test_cycle;
                }
            }
            
            $releaseStageLastKnown = !empty($reData->release_stage)?
                                  $reData->release_stage:
                                  'N/A';
            $xmlObj = reportsuiteXmlProcessor::processTestResultsXml($reData->reid);
            $testResultsData = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData($xmlObj);
            $getRunBy = reportsuiteXmlProcessor::getParentChildElementByElementName(
                                        $xmlObj, 'runBy');
            
            $runBy = !empty($getRunBy)?$getRunBy:'N/A';
            
            $reidList[] = $reData->reid;
            $reAdditionalDets[$reData->reid] = array( 
                               'uuid' => $reData->uuid,
                               'runDateTime' => $reData->run_datetime_start,
                               'browserAssign' => $reData->browser_assign,
                               'testEnvironment' => $reData->test_environment,
                               'releaseStage' => $releaseStageLastKnown,
                               'testCycle' => $reData->test_cycle,
                               'runBy' => $runBy,
                               'durationInSec' =>  reportsuite::processTotalDurationBySuite($testResultsData),
                                );
            
            if($reData->run_mode == 'GRID')
            {
                $reAdditionalDets[$reData->reid]['gridDurationInSec'] = 
                        reportsuite::calculateTotalDurationByStartStopTime(
                                $reData->run_datetime_start, $reData->run_datetime_stop);
                    
            }
        }
        
        if(empty($reidList)) return;
        
        $automationResultStoreUnsorted = reportsuitequeries::getAllRunResultFromListOfReid($reidList);
        $automationResultStore = reportsuitequeries::adjustRunResultStoreToRunExecutionStore($automationResultStoreUnsorted);
        
        foreach ($reAdditionalDets as $reid => $value)
        {
            $automationResultStore[$reid]['additionalDets'] =  $value;
        }
        return $automationResultStore; 
    }
    
    public static function sortAutoResultStoreByTestCycle($automationResultStore)
    {
        $automationResultStoreAdjustment = array();
        
        foreach($automationResultStore as $reid => $resultStore)
        {
            if($resultStore['additionalDets']['testCycle'] != "MR")
            {
                $automationResultStoreAdjustment[
                    $resultStore['additionalDets']['testCycle']
                    ][$reid] = $resultStore;
            }
        }
        
        return $automationResultStoreAdjustment;
    }
  
    /**
     * Convert into Table Data Structure
     */
    public static function processRoutesUsageIntoTDS($routeUsage)
    {
        $arr=array('rowToGenerate' => 0);
      foreach($routeUsage as $ru)
      {
          $arr[$ru['status']][] = array('routes' => $ru['routes'], 'tcs' => $ru['testCases']);
          if($arr['rowToGenerate'] < count($arr[$ru['status']]))
              $arr['rowToGenerate'] = count($arr[$ru['status']]);
      }
      return $arr;
    }
    
}

?>
        