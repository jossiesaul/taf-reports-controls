<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reportsuite High Level Analysis Class
 * for higher level expanding over multiple amount of cases
 * 
 *
 * @author jossie
 */
class reportsuitehlanalysis extends reportsuiteanalysis {
    
    
    public static function processCustomerSummaryAnalysisData($tpid, 
            $queries = array(), $duration  = array())
    {
        //default to last 10 runs
        if (!isset($duration))
        {
            
        }
        //Get a run result from duration  default is 
    }
    
    public static function compileListOfTestCasesForOverallProject($listOfProject)
    {
        $listOfTestCasesPerProject = array();
        foreach($listOfProject as $tpid => $projectInfo)
        {
            $listOfTestCases = reportsuite::listOfTestCases($tpid);
            foreach ($listOfTestCases as $tcid => $testCaseInfo) 
            {
                $listOfTestCasesPerProject[$tpid][$testCaseInfo['scenario']][$tcid] = $testCaseInfo;
            }
        }
        return $listOfTestCasesPerProject;
    }
    
    public static function getTestCaseGrowthByTimeSpan($tpid, $timespan)
    {
        return reportsuite::listOfTestCases($tpid, $timespan);
    }
   
    public static function getTestCaseGrowthByReleasePhase($tpid, $releasePhase)
    {
        $timespan = array(
            'from' => reportsuitequeries::getDateOfReleasePhase($tpid, $releasePhase, 'ASC'),
            'to' => reportsuitequeries::getDateOfReleasePhase($tpid, $releasePhase, 'DESC')
                );
        
        return reportsuite::listOfTestCases($tpid, $timespan);
    }
    
    public static function compileProjectCoverageDetail($listOfProject)
    {
        $projectCoverageDetail = array();
        
        foreach($listOfProject as $tpid => $projectInfo)
        {
            $projectCoverageDetail[$tpid] = 
                self::processCoverageAnalysisOnProject($tpid);
        }
        return $projectCoverageDetail;
    }
    
    
    /**
     *  Method to get overall analysis of the project progression
     * @param type $listOfProject
     */
    public static function projectOverallAnalysisData($listOfProject, $dept)
    {
            $compileAnalyticData = array();
            
            foreach ($listOfProject as $project)
            {
                $tpid = $project['tpid'];
                $compileAnalyticData[$tpid] = 
                        array('totalNumOfTestCases' => self::getNumberOfTestCasesOnProject($tpid),
                              'totalNumOfActiveTestCases' => 
                              self::getNumberOfActiveTestCasesOnProject($tpid, $dept),
                              'lastRunPassRate' => self::getLastPassRate($tpid, $dept),
                              'averagePassRate' => self::getAveragePassRate($tpid, $dept),
                              'passToFailRatio' => reportsuitequeries::passToFailRatio($tpid, $dept), 
                              'projectInfo' => $project,
                              'growthOfTestCases' => self::getGrowthOfTestCasesOnProject($tpid),
                        );
            }
            return $compileAnalyticData;
    }
    
    public static function getAllTestCasesResultsDataToDate($tpid, $dept)
    {
        return self::grabAllTestCasesResultData($tpid, $dept);
    }
    
    public static function getAllTestCaseResultsDataByTimeSpan($tpid, $dept, $timespan)
    {
        return self::grabAllTestCasesResultData($tpid, $dept, 
                        array('timespan' => $timespan));
    }
    
    public static function getAllTestCaseResultsByReidCollectionSet($reidCollection)
    {
        return self::grabAllTestCasesResultData(0, 'PST', 
                        array('reidCollection' => $reidCollection));
    }
    
    public static function countTotalNumberOfCasesRun($testRunResults)
    {
        return count(array_keys($testRunResults));
    }
    
    public static function getTotalTestRunResultOutcome($testRunResults)
    {
        $totalResultOutcome = array('Passed' => 0, 'Failed' => 0, 'Warning' => 0);
        foreach($testRunResults as $reid => $dt){
            foreach(reportsuiteresult::getResutStatusCriteria() as $oc)
            {
                $totalResultOutcome[$oc] +=  $dt['CombineTotal'][$oc]; 
            }
        }
        return $totalResultOutcome;
    }
    
    public static function getTotalActionStepResultOutcome($asDataStore)
    {
        $totalActionStepOutcome = array('Passed' => 0, 'Failed' => 0, 'Warning' => 0);
        foreach($asDataStore as $reid => $dt){
            foreach(reportsuiteresult::getResutStatusCriteria() as $oc)
            {
                $totalActionStepOutcome[$oc] +=  $dt['statisticalData']['total']
                                                                                ['actionStepsStats']['numOfSteps'.$oc]; 
            }
        }
        return $totalActionStepOutcome;
    }
    
    public static function getTotalFailureCommonality($asDataStore)
    {
        $failureCommonalityTotal = array();
        foreach($asDataStore as $reid => $dt){
           // drupal_set_message('<pre>' . print_r($dt['statisticalData']['total']['failureCommonality'], 1) . '</pre>');
            
            foreach($dt['statisticalData']['total']['failureCommonality'] as $failures){
                
                $i = reportsuiteanalysishelper::searchFailureCommonalityDuplicateErrorMsg(
                        $failureCommonalityTotal, $failures['errMsg']);
        
                if($i !== FALSE){
                    $failureCommonalityTotal[$i]['counter'] +=$failures['counter'];
                    array_merge($failureCommonalityTotal[$i]['testCases'], $failures['testCases']);
                    unset($i);
                }else{
                    $failureCommonalityTotal[] = $failures;
                }
            }
        }
        return $failureCommonalityTotal;
    }
    
    public static function getTotalDurationFromTestRunResults($testRunResults, $durationType)
    {
        $totalDur = 0;
        if(empty($testRunResults)) return $totalDur;
        foreach($testRunResults as $reid => $dt){
            if(isset($dt['additionalDets'][$durationType]))
                $totalDur += $dt['additionalDets'][$durationType];
        }
        return $totalDur;
    }
    
    public static function getAveragePassRateFromTestRunResults($testRunResults)
    {
        $prArr = array();
        foreach($testRunResults as $reid => $dt){
            $prArr[] = floatval(reportsuitequeries::calculatePassRate(
                                $dt['CombineTotal']['Total'], $dt['CombineTotal']['Total Passed']));
        }
        return number_format(array_sum($prArr) / count($prArr),2);
    }
    
    public static function gatherTopRunnersFromTestRunResults($testRunResults)
    {
        $runners = array();
        foreach($testRunResults as $reid => $dt){
            $runBy = $dt['additionalDets']['runBy'];
            if(!isset($runners[$runBy]))
                    $runners[$runBy] = 1;
            else
                    $runners[$runBy]++;
        }
        return $runners;
    }
    
    public static function gatherTopEnvFromTestResults($testRunResults)
    {
        $environments = array();
        foreach($testRunResults as $reid => $dt){
            $env = $dt['additionalDets']['testEnvironment'];
            if(!isset($environments[$env]))
                    $environments[$env] = 1;
            else
                    $environments[$env]++;
        }
        return $environments;
    }
    
    public static function gatherTestCaseResultHistoryFromRuns($asDataStore)
    {
        $testCaseResults = array();
        foreach($asDataStore as $reid => $data){
            foreach ($data['analysisRawData'] as $scenario => $tcDt){
                foreach($tcDt as $testCaseId => $result){
                    //drupal_set_message('<pre>' . print_r($result, 1) . '</pre>');
                    $testCaseResults[$testCaseId][$reid] = array(
                        'outcome' => $result['outcome'],
                        'failDetail' => $result['failDetail'],
                        );
                }  
            }
        }
        return $testCaseResults;
    }
    
    public static function gatherAllAnalyticalAndStatisticalDataByArrayOfReid($reidArr)
    {
        $asDataStore = array();
        foreach ($reidArr as $reid)
        {
             $xmlObj = reportsuiteXmlProcessor::injectRerunPatchElementIntoXml($reid, 
                                reportsuiteXmlProcessor::processXmlIntoObject(
                                reportsuiteXmlProcessor::loadXmlByReid($reid)));
            $testCasesData = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData($xmlObj); 
            $otr = reportsuite::testResultsIntoTDS(
                            reportsuiteXmlProcessor::getOverallResultsFromSuites($xmlObj));
            $analysisRawData = reportsuiteanalysis::runResultAnalysisRawData($reid, $xmlObj, $testCasesData);
            $statisticalData = reportsuiteanalysis::processAnalysisDataToStatisticalOutput($analysisRawData);
            $asDataStore[$reid]['statisticalData']['overallTestResult'] = $otr;
            $asDataStore[$reid]['analysisRawData']  = $analysisRawData;
            $asDataStore[$reid]['grtDuration'] = reportsuite::calculateTotalDurationByStartStopTime(
                                    $xmlObj->runDateTimeStart, $xmlObj->runDateTimeStop);
            $asDataStore[$reid]['statisticalData'] = $statisticalData;
            
        }
        
        return $asDataStore;
    }
    
    /**
     * Get and Compile all Test Result Data
     * 
     * @param type $dept
     * @param type $timespan
     * @return type
     */
    public static function compileAllProjectAutomationTestCaseResultByTimeSpan($dept, $timespan)
    {
        $projectList = reportsuitequeries::getAllTpid();
        
        $automationResultStore = array();
        $automationResultStoreProjectCombined = array();
        
        foreach($projectList as $tpid => $value)
        {
            $automationResultStore[$tpid] = 
                                self::getAllTestCaseResultsDataByTimeSpan($tpid, $dept, $timespan);
        }
        
        $resultStringArr = array('Total', 'Total Passed', 'Passed', 'Warning', 'Failed', 'TAFError');
        foreach ($automationResultStore as $tpid => $runExecutionDetail)
        {
            foreach ($runExecutionDetail as $reid => $runResultDetail)
            {
                $date = date('Y-m-d', strtotime($runResultDetail['additionalDets']['runDateTime']));
             //   drupal_set_message('<pre>' . print_r($runResultDetail['CombineTotal'], 1) . '</pre>');
                if(!isset($automationResultStoreProjectCombined
                                [$date]))
                {
                    foreach($resultStringArr as $str)
                    {
                        $automationResultStoreProjectCombined[$date]['CombineTotal'][$str] = 
                                $runResultDetail['CombineTotal'][$str];
                    }
                }
                else
                {
                    foreach($resultStringArr as $str)
                    {
                        $automationResultStoreProjectCombined[$date]['CombineTotal'][$str] += 
                                $runResultDetail['CombineTotal'][$str];
                    }
                    
                 }
                 
                 $automationResultStoreProjectCombined[$date]['additionalDets']['runDateTime'] = 
                         $runResultDetail['additionalDets']['runDateTime'];
            }
        }
        ksort($automationResultStoreProjectCombined);
        return $automationResultStoreProjectCombined;
    }
   
   
}

?>
