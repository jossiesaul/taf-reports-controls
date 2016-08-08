<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reportsuite
 *
 * @author jossie
 */
class reportsuite {
    //put your code here
    
    public static $coverageTypes = array('Flight Type',
                                         'Search Type',
                                         'PAX Type', 
                                         'Ancillaries',
                                         'Payment Type',
                                         'Basic Features');
    
    public static $ftpUsername = 'automation';
    
    public static $ftpPassword = 'password';
    
    public static $copyrightText = 'Datalex &copy; 2014';
    
    public static $testCycleDefinition = array(
        'PR' => 'Pre-Release', 
        'RC' =>'Release Candidate',
        'UAT' => 'User Acceptance Testing',
        'GL' => 'Go-Live',
        'TEST' => 'NA'
        );
    
    /**
     * 
     * @param type $outcome
     * @param type $contrastSelection Choice of the following; <br /> -Lite<br /> - Dark
     * @return string
     */
    public static function getPassFailWarningCSSStyle($outcome, $contrastSelection = null)
    {
        
        if(!empty($contrastSelection)) $outcome.= $contrastSelection;
        
        //drupal_set_message($outcome);
        
        $style = "";
        switch ($outcome)
        {
            case 'Passed':
                $style = 'background-color:#B2FFB2;'; 
                break;
            case 'Warning':
                $style = 'background-color:#FFEB99;';
                break;
            case 'Failed':
                $style = 'background-color:#FFB2B2;';
                break;
            case 'TAFError':
                $style = 'background-color:#D0D0D0;';
                break;
            case 'PassedLite':
                $style = 'background-color:#;'; 
                break;
            case 'WarningLite':
                $style = 'background-color:#;';
                break;
            case 'FailedLite':
                $style = 'background-color:#;';
                break;
            case 'TAFErrorLite':
                $style = 'background-color:#;';
                break;
            case 'PassedDark':
                $style = 'background-color:#8ECC8E;'; 
                break;
            case 'WarningDark':
                $style = 'background-color:#E6D48A;';
                break;
            case 'FailedDark':
                $style = 'background-color:#CF9090;';
                break;
            case 'TAFErrorDark':
                $style = 'background-color:#BBBBBB;';
                break;
            default :
                $style = '';
                break;
        }
        return $style;
    }

    public static function getRunTypeEnum($runFlag)
    {
        $runTypeDets = array(
          0 => array(
            'type' => 'official',
            'name' => 'Official',
            'description' => 'Official Run - these run are visually published on every reports',
            'rules' => array(),
          ),
          1 => array(
            'type' => 'badRun',
            'name' => 'Bad Run',
            'description' => '',
            'rules' => array(),
          ),
          2 => array(
            'type' => 'testRun',
            'name' => 'Test Run',
            'description' => 'Test Runs - these run can be filtered out ',
            'rules' => array(),       
          ),
          3 => array(
            'type' => 'maintenanceRun',
            'name' => 'Maintenance',
            'description' => 'Test Runs - Usage to be reviewed, although these run can be filtered out',
            'rules' => array(),
          ),
          4 => array(
            'type' => 'sanityRun',
            'name' => 'Sanity Run',
            'description' => 'Sanity Run - Usage to be reviewed, although these run can be filtered out',
            'rules' => array(),
          ),
          69 => array(
            'type' => 'reRun',
            'name' => 'Re-run',
            'description' => '',
            'rules' => array(),
          )
        );
        
        return $runTypeDets[$runFlag];
    }

    public static function getFailPrioSwitch($failPrio)
    {
        $failPrioIntrepret = 0;
        switch($failPrio)
        {
            case 1:
                $failPrioIntrepret = 'Failed';
                break;
            case 2:
                $failPrioIntrepret = 'Failed';
                break;
            case 3:
                $failPrioIntrepret = 'Failed';
                break;
            case 4:
                $failPrioIntrepret = 'Warning';
                break;
        }
        
        return $failPrioIntrepret;
    }

    public static function getOverrideOutcomeReasons()
    {
        $reasons = array('TAF Common Error - Case run was successful',
                        );
        
        return $reasons;
    }
    
    /**
     * Function to proccess the individual test case outcome detail in the XML
     * 
     * @param type $reid
     * @param type $testCaseId
     * @return type
     */
    public static function getTestCaseOutcomeData($reid, $testCaseId)
    {
        $testCasesData = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData(
                     reportsuiteXmlProcessor::processXmlIntoObject(
                     reportsuiteXmlProcessor::loadXmlByReid($reid)));
        
        return $testCasesData[$testCaseId];
    }
    
    /**
     * 
     * Get and Determine the Release Phase Dates using the automationResultStoreAll
     * 
     * @param type $automationResultStore
     */
    public static function getReleasePhaseDates($automationResultStoreAll)
    {
        $releasePhaseData = array();
        $automationResultStoreAll = reportsuiteanalysis::
                sortAutoResultStoreByTestCycle($automationResultStoreAll);
        
        foreach ($automationResultStoreAll as $testCycle => $data)
        {
            foreach($data as $reid => $result)
            {
                if(!empty($result['additionalDets']['runDateTime']))
                {
                    $releasePhaseData[$testCycle] = date('Y-m-d', 
                            strtotime($result['additionalDets']['runDateTime']));
                }
            }
        }
        return $releasePhaseData;
    }
    
    public static function processResultDetailPageSubMenuItem($currentPageDisplayed)
    {
        
    }
    
     /**
     * Get Real Value Duration
     * 
     * @param Array $testResultData taken from self::processXmlIntoTestCases
     ResultData() 
     * @return int Return Duration By Seconds
     */
    public static function processTotalDurationBySuite($testResultData)
    {
        //Calculate by seconds
        $duration = 0;
        //drupal_set_message('<pre>'.  print_r($testResultData, 1).'</pre>');
        foreach ($testResultData as $testCaseId => $data)
        {
            if($testCaseId != 'uuid')
            {
                //drupal_set_message($data['duration']);
                $duration += self::parseHHMMSSDurationIntoTotalSeconds($data['duration']);;
            }
        }
        //drupal_set_message("Total Duration: $duration");
        return $duration;
    }

    public static function calculateTotalDurationByStartStopTime($start, $stop)
    {
        return strtotime($stop) - strtotime($start);
        
    }
    
    public static function processExecutionListData($executionList)
    {
        $executionListData = array();
        
        $multipleIdenticalTestCase = false;
        $identicalTestCaseIterator = 1;
        $prevTestCaseID = 'Start';
        
        $execListDataRaw = explode('<br />', $executionList);
       //drupal_set_message('<pre>'.  print_r($execListDataRaw, 1).'</pre>');
        
        foreach ($execListDataRaw as $strLine)
        {
            
            ////if (preg_match("/TestScenario/", $strLine))
            if (substr($strLine, 0, 12) == "TestScenario")
            {
               $testScenarioInfo = explode("::", $strLine);
               $testScenarioName = $testScenarioInfo[1];
               //drupal_set_message($testScenarioName);
                //drupal_set_message('<pre>'.  print_r($testScenarioInfo, 1).'</pre>');
            }
            
            //if (preg_match("/TestCase/", $strLine))
            if (substr($strLine, 0, 8) == "TestCase")
            {
              //  drupal_set_message($testCaseInfo[2]);
            //  drupal_set_message('<pre>' . print_r($testCaseInfo, 1) . '</pre>');
             $testCaseInfo = explode("::", $strLine);
             $testCaseID = $testCaseInfo[2];
             
             
            if($prevTestCaseID == $testCaseInfo[2])
             {
                 $identicalTestCaseIterator++;
                 $multipleIdenticalTestCase = true;
                 $testCaseID .= 'Data'.$identicalTestCaseIterator;
             }
             else
             {
                $identicalTestCaseIterator = 1;
                $multipleIdenticalTestCase = false;
             }
             $prevTestCaseID = $testCaseInfo[2];
             $executionListData[$testScenarioName][$testCaseID] = array();
             //drupal_set_message('<pre>'.  print_r($testCaseInfo, 1).'</pre>');
            }
            
            if(preg_match("/Selenium/", $strLine) || preg_match("/Condition/", $strLine))
            {
              //drupal_set_message($strLine);
                array_push($executionListData[$testScenarioName][$testCaseID],explode("::", $strLine));
            }
            
        }
        
      //  drupal_set_message('<pre>'.  print_r($executionListData, 1).'</pre>');
        
        return $executionListData;
    }
    
    
    public static function processUIFriendlyExecutionListByTestCase($executionList, $testCase, $testResData)
    {
        $stepsColorAddon = '';
        $output = '';
        $caseFailed = false;
        $step = 1;
        //$actionStepsFailError = '';
        
        if(!isset($executionList))
        {drupal_set_message('ExecutionList Empty!', 'error'); return;}
        
        $actionStepsFailError = reportsuite::processFailingActionStepsByTestCase($testResData[$testCase]);
        $output .= '<table style="border:5px solid black;border-spacing:10px;">';
        $output .= '<tr><th colspan=6><center>Execution RunSteps</center></th></tr>';
        $output .= '<tr><th>Step #</th><th>(A) Action / (C) Condition</th><th>Fail Priority</th>
            <th>Data # 1</th><th>Data # 2</th><th>Data # 3</th></tr>';
//        drupal_set_message($testCase);
        //drupal_set_message('<pre>' . print_r($executionList, 1) . '</pre>');
        foreach ($executionList as $execData)
        {
            if(preg_match("/Selenium/", $execData[0]) )
                $action = trim(str_replace('Selenium.', 'A.', $execData[0]));
            else
                $action = trim(str_replace('Condition.', 'C.', $execData[0]));
            
            $contrastSel = $action == 'A.waitPageIsLoaded'?'Dark':'';
            //$contrastSel = $action == 'C.waitPageIsLoaded'?'Dark':'';

            if ($testResData[$testCase]['outcome'] == 'Passed')
            {
                 $stepsColorAddon = 'style="1px solid black;'.reportsuite::getPassFailWarningCSSStyle('Passed', $contrastSel).';"';
            }

            //drupal_set_message($contrastSel);
            if(!empty($actionStepsFailError))
            {
                if(!empty($actionStepsFailError[$step]))
                {
                    if(in_array($actionStepsFailError[$step], array('Failed','Warning')))
                    {
                        $stepsColorAddon = in_array($actionStepsFailError[$step], array('Failed','Warning'))?
                                       'style="border:1px solid black;'.reportsuite::getPassFailWarningCSSStyle($actionStepsFailError[$step]).';"':
                                       '';
                    }
                    if($actionStepsFailError[$step] == 'Failed')
                    {
                        $caseFailed = true;
                    }
                }
                else
                {
                    if(!$caseFailed)
                        $stepsColorAddon = 'style="1px solid black;'.reportsuite::getPassFailWarningCSSStyle('Passed', $contrastSel).';"';
                }
            }

           // if($stepsColorAddon == '')
            //$bgcolorAddon = $action === 'waitPageIsLoaded'?'style="background-color:#D0D0D0;"':'';
            //drupal_set_message('<pre>' . print_r($execData, 1) . '</pre>');
            //$output .= $execData[0].'-'.$execData[1].'-'.$execData[2].'-'.$execData[3].'<br />';
            $output .= '<tr '.$stepsColorAddon.'>';
            $output .= '<td>'.$step.'</td>';
            $output .= '<td>'.$action.'</td>';
            $output .= '<td>'.$execData[1].'</td>';
            $data2 = !isset($execData[2])?"":$execData[2];
            $output .= '<td>'.$data2.'</td>';
            $data3 = !isset($execData[3])?"":$execData[3];
            $output .= '<td>'.$data3.'</td>';
            $data4 = !isset($execData[4])?"":$execData[4];
            $output .= '<td>'.$data4.'</td>';
            $output .= '</tr>';
            $step++;
        }
        $output .= '</table>';
        return $output;
    }
    
    public static function processFailingActionStepsByTestCase($testCaseResData)
    {
        if (empty($testCaseResData)) return NULL;
        
        preg_match_all('/ActionStep#\d+{F:\d}/', $testCaseResData['failDets'], $matches);
        //drupal_set_message('<pre>'.  print_r($testCaseResData, 1).'</pre>');
        if(empty($matches) || $testCaseResData['outcome'] == 'Passed') return NULL;
        
        $failingActionSteps = array();
        foreach($matches as $match)
        {
            foreach ($match as $actionStep)
            {
                $step = self::parseStepFromActionStep($actionStep);
                $failPrio = self::parseFailPrioFromActionStep($actionStep);
                $failingActionSteps[$step] = self::getFailPrioSwitch($failPrio);
            }
        }
        return $failingActionSteps;
    }
    
    public static function splitErrorMessages($failDetails)
    {
        $matches = preg_split('/ActionStep#\d+{F:\d} - /', $failDetails);
        return array_filter($matches);
    }
    
    public static function processDetailMessageFromRunResults($details)
    {
        //drupal_set_message($details);
        $details = preg_replace('/ActionStep#/', '<br />Step: ', $details);
        $details = preg_replace('/{F:\d}/', ' ', $details);
        $details = preg_replace('/--- Error Notice \d+ ---/', ' <br />Error Notice <br /> ', $details);
        
        if(preg_match('/F:4/', $details)){
            drupal_set_message("its a 4");
            $details = '<span style="background:#000;">'.$details.'</span>';
        }
        
        return $details;
    }
    
    public static function processTotalDurationBySeconds($seconds, $max_periods, $limitToHours = false)
    {
            $periods = $limitToHours?
                    array( 'hour' => 3600, 'min' => 60, 'sec' => 1):
                    array('year' => 31536000, 'month' => 2419200, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
        $i = 1;
        foreach ($periods as $period => $period_seconds) {
            $period_duration = floor($seconds / $period_seconds);
            $seconds = $seconds % $period_seconds;
            if ($period_duration == 0)
                continue;
            $duration[] = $period_duration . ' ' . $period . ($period_duration > 1 ? 's' : '');
            $i++;
            if ($i > $max_periods)
                break;
        }
        if (is_null($duration))
            return 'just now';
        return implode(' ', $duration);
    }
    
    public static function parseStepFromActionStep($actionStep)
    {
        return substr($actionStep, strpos($actionStep, "#")+1, (strpos($actionStep, "{")+1)-(strpos($actionStep, "}")+2));
    }
    
    public static function parseFailPrioFromActionStep($actionStep)
    {
        return substr($actionStep, strpos($actionStep, "F:")+2, (strpos($actionStep, "}")+1)-(strpos($actionStep, "}")+2));
    }
    
    public static function parseHHMMSSDuration($duration)
    {
        $splitDur = explode(":", $duration);
        
        $pluralAdd1 = $splitDur[0] > 1 ? 's':'';
        $pluralAdd2 = $splitDur[1] > 1 ? 's':'';
        $pluralAdd3 = $splitDur[2] > 1 ? 's':'';
        
        $hrs = $splitDur[0] == 0 ? '' : $splitDur[0].' Hour'.$pluralAdd1.', ';
        $mins = $splitDur[1] == 0 ? '' : $splitDur[1].' Minute'.$pluralAdd2.', ';
        $secs = $splitDur[2] == 0 ? '' : $splitDur[2].' Second'.$pluralAdd3.' ';
        
        $parseDur = array('hrs' => $hrs,
                         'min' => $mins,
                         'sec' => $secs,
                        );
        
        return $hrs.$mins.$secs;
    }
    
    public static function parseHHMMSSDurationIntoTotalSeconds($duration)
    {
        $durationInSeconds = 0;
        $splitDur = explode(":", $duration);
        
        $hourInSec = $splitDur[0]*3600;
        $minuteInSec = $splitDur[1]*60;
        $seconds = $splitDur[2];
        
        $durationInSeconds += $hourInSec + $minuteInSec + $seconds;
        
        return $durationInSeconds;
    }
   
    
    public static function parseCoverageOnTestCaseId($testCaseId)
    {
        $coverageCodes = explode('_', $testCaseId);
            $coverageInfo = array();                    
             $coverageCatalog = reportsuiteXmlProcessor::getCoverageMatrixCatalog();
            $numOfPassengerTotal = 0;
            foreach ($coverageCodes as $aliasKey)
            {
               
               if(!in_array($aliasKey, array('VISA3D', 'MASTER3D', 'MCARD3D')))
               {
                   //Before Numbers are being pull out of the key put into a temp var
                   $numInAliasKey = $aliasKey;
                   
                   $numbers = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
                   $aliasKey = str_replace($numbers, '', $aliasKey);
                   
                   //Get Passenger Number
                   if(in_array($aliasKey, array('ADT', 'CNN', 'CHD', 'INF', 
                       'UNN', 'UNMR', 'CMP')))
                   {
                       $numOfPassengerTotal += intval($numInAliasKey) <= 1 ? 1 : 
                                                intval($numInAliasKey);
                   }
               }
               
               $coverageInfo[$aliasKey] = reportsuiteXmlProcessor::coverageAlias($aliasKey);
               
               //Putting the coverage Information into Catalog Types
               $coverageType = $coverageInfo[$aliasKey]['type'];
               if(isset($coverageCatalog[$coverageType]))
               {
                $coverageCatalog[$coverageType][$aliasKey] = $coverageInfo[$aliasKey];
               }
               
               //drupal_set_message();
               //$this->coverageCatalog[$coverageInfoRaw[$aliasKey]['type']] = ;
            }
                
      return array('testCaseID' => $testCaseId,
                   'details' => array('totalPaxCount' => $numOfPassengerTotal,
                                      'coverage' => $coverageCatalog),
                  );
            
            
    }
    
    /**
     * Main Method to generate Switch Graph Links
     * 
     * @param String $currentGraph
     * @param String $pageAt
     * @param Array $urlParams 
     * 
     * Parameters Allowed
     * -------------------
     * <br /><br />
     * tpid - TAF Project ID from DB <br />
     * customer - Customer/Project Name<br />
     * pos - Point Of Sale<br />
     * dept = Name of department<br />
     * 
     * @return Arrays of Links
     */
    public static function switchGraphLinksController($currentGraph, $pageAt, $urlParams = array())
    {
        $graphLinks = '';
        
        //Set up format of URL
        switch ($pageAt)
        {
            case 'summary':
                $graphOption = array('historicalRun' => 'Historical Run Line',
                                     'historicalRunArea' => 'Historical Run Area',
                                     'historicalPassRate' => 'Historical Pass Rate Trend',
                                     'historicalDuration' => 'Total Execution Time Trend'
                               );
                $graphLinks .= self::switchGraphLinksGenerator($currentGraph, $urlParams, $graphOption);
                break;
            
            case 'thisPage':
                break;
            
            default:
                drupal_goto(drupal_not_found());
                break;
        }
        return $graphLinks;
    }
    
    /**
     * function to route the graph type to graph generator class
     * 
     * @param type $currentGraph
     * @param type $urlParams
     * @param type $graphOption
     * @return string
     */
    protected static function switchGraphLinksGenerator($currentGraph, $urlParams, $graphOption = array())
    {
        $graphLinks = '| ';   
        foreach ($graphOption as $graphOpt => $graphLinkTitle)
        {
             $urlLink = 'reportsuite/summary/'.
                            $urlParams['tpid'].'/'.
                            $urlParams['projectName'].'-'.$urlParams['pos'].'-'.$graphOpt.'/'.
                            $urlParams['dept'];
            
            $graphLinks .= $currentGraph == $graphOpt?$graphLinkTitle:
                    l($graphLinkTitle, $urlLink);
            $graphLinks .= ' | ';
        }
        return $graphLinks;
    }
    
    public static function sublinksMainPage($currentPage, $dept)
    {
        $links = array('Summary' => l('Summary', 'reportsuite/mainPage/Summary/PST/null').' | ',
                       'Analysis' => l('Analysis', 'reportsuite/mainPage/Analysis/PST/null').' | ',
                       'Run Automation' => l('Run Automation', 'projectcontrol/mainPage/launcher/PST/reportsuite'),
                 );
        
        $t = 1;
        $linkStrings = '<div class="rrSublinks">';
        foreach ($links as $linkKey => $link)
        {
                $linkStrings .= $link;
            $t++;
        }
        $linkStrings .= '</div>';
        return $linkStrings;
    }
    
    public static function sublinksRunResultDetails($currentPage, $reid)
    {
        $links = array('Result Details' => l('Result Details', 'reportsuite/resultDetail/'.$reid.'/null/null').' | ',
                       'Run Analysis' => l('Run Analysis', 'reportsuite/runAnalysis/'.$reid.'/summary/null').' | ',
                       'Coverage Matrix' => l('Coverage Matrix', 'reportsuite/coverageMatrix/'.$reid.'/null/null').' | ',
                       'RunStep Walkthrough' => l('Run Step Walkthrough', 'reportsuite/runLog/'.$reid.'/null/null',
                               array('attributes' => array('id' => 'test'))).' | ',
                     //  'Historical Stats' => 'Historical Stats | ',
                       'Download' => popup(array('title' => 'Download',
                                'text' => self::downloadLinksRunResultDetails($reid),
                                'effect' => 'slide-down')).' | ',
                       'Export Results' => l('Export Results', 'reportsuite/exportResults/'.$reid.'/null/null').' | ',
                       'Control Suite' => l('Control Suite', 'projectcontrol/suite/'.reportsuitequeries::getTpidByReid($reid).'/null/null'),
                 );
        
        $linkStrings = '<div class="rrSublinks">';
        foreach ($links as $link)
        {
            $linkStrings .= $link;
        }
        $linkStrings .= '</div>';
        return $linkStrings;
    }
    
    public static function sublinksCustomerAnalysisDisplayOptions($currentPage, $tpid)
    {
        $links = array('Daily' => l('Daily', 'reportsuite/automationAnalysis/'.$tpid.'/timespan/daily', 
                            array('attributes' => array('id' => 'pageSublinks'))),
                                  'Weekly' => l('Weekly', 'reportsuite/automationAnalysis/'.$tpid.'/timespan/weekly', 
                            array('attributes' => array('id' => 'pageSublinks'))),
                                  'Monthly' => l('Monthly', 'reportsuite/automationAnalysis/'.$tpid.'/timespan/monthly', 
                            array('attributes' => array('id' => 'pageSublinks'))),
                                  'ReleasePhase' => l('ReleasePhase', 'reportsuite/automationAnalysis/'.$tpid.'/releasePhase/show', 
                            array('attributes' => array('id' => 'pageSublinks'))),
                                  'TestCaseGrowth' => l('Test Case Growth', 'reportsuite/automationAnalysis/'.$tpid.'/testcaseGrowth/show', 
                            array('attributes' => array('id' => 'pageSublinks'))),
            );
        
        $linkStrings = '';
        foreach ($links as $linkKey => $link)
        {
            $linkStrings .= $link;
        }
        
        return $linkStrings;
    }
    
    public static function downloadLinksRunResultDetails($reid)
    {
        $reDets = reportsuitequeries::getRunExecutionDetailsByReid($reid);
        $tpDets = reportsuitequeries::getTpbyTpid(reportsuitequeries::getTpidByReid($reid));
        
        $hostPrefix = 'ftp:'.self::$ftpUsername.':'.self::$ftpPassword.
                    '@'.$reDets->local_host_ip;
        
        $selLogUrlPath = $hostPrefix.'/OldSeleniumLogs/'.$tpDets->customer.'_'.
                    $reDets->uuid.'.zip';
        
        $reportUrlPath = $hostPrefix.'/OldReports/'.$tpDets->customer.'_'.
                    $reDets->uuid.'.zip';
        
        $links = '';
        $links .= l('Selenium Logs', $selLogUrlPath);
        $links .= '<br />-<br />';
        $links .= l('Test Report', $reportUrlPath);
        
        return $links;
    }
    
    public static function sublinksCustomerSummary($currentPage, $tpid)
    {
        $tpData = reportsuitequeries::getTpbyTpid($tpid);
        $links = array('Report Summary' => l('Report Summary', 'reportsuite/summary/'.$tpData->tpid.
                        '/'.$tpData->customer.'-'.$tpData->pos.'-historicalRunArea/PST').' | ',
                       #'Historical Runs' => 'Historical Runs | ',
                       'Automation Analysis' => l('Automation Analysis', 'reportsuite/automationAnalysis/'.$tpid.'/timespan/weekly').' | ',
                       'Control Suite' => l('Control Suite', 'projectcontrol/suite/'.$tpid.'/null/null'),
                 );
        
        $linkStrings = '<div class="rrSublinks">';
        foreach ($links as $linkKey => $link)
        {
            $linkStrings .= $link;
        }
        $linkStrings .= '</div><br />';
        return $linkStrings;
    }
    
    /**
     * 
     * @param type $reid
     * @param type $tpid
     * @todo Update the test cases if description or actionSteps have changed
     */
    public static function probeTestCases($reid, $tpid = null)
    {
        if($tpid == null) $tpid = reportsuitequeries::getTpidByReid($reid);
        $runResultData = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData(
                                        reportsuiteXmlProcessor::processTestResultsXml($reid));
        $execListData = reportsuite::processExecutionListData(
                                     reportsuitequeries::getRunExecutionList($reid));
        $newCasesDetected = false;
        
        $newTestCases = array();
        foreach ($runResultData as $testCaseId => $resData)
        {
            if($testCaseId != 'uuid')
            {
                $testCaseData = reportsuitequeries::getTestCaseByTestCaseId($testCaseId, $tpid);
                if(empty($testCaseData))
                {
                     $newCasesDetected = true;
                     $newTestCases[] = array('testCaseId' => $testCaseId,
                                             'scenario' => $resData['scenario'],
                                             'description' => $resData['description'],
                                             'numOfActionSteps' => 
                                              count($execListData[$resData['scenario']][$testCaseId]),
                                             );
                }
                else
                {
                    
                }
            }
        }
        
        if($newCasesDetected)
        {
            reportsuitequeries::setTestCaseByTestCaseId(null, $tpid, $newTestCases, true);
        }
    }
    
    /**
     * 
     * @param type $tpid
     * @return type
     */
    public static function listOfTestCases($tpid, $timespan = null)
    {
      $testCaseList = array();
      $tclResult = is_array($timespan)?
              reportsuitequeries::getTestCasesByDate($tpid, $timespan):
              reportsuitequeries::getTestCasesByTpid($tpid);
        foreach($tclResult as $testCase)
        {
            $testCaseList[$testCase->tcid] = array('testcase_id' => $testCase->testcase_id,
                                                   'description' => $testCase->description,
                                                   'scenario' => $testCase->scenario,
                                                   'num_of_actionsteps' => $testCase->num_of_actionsteps,
                                                   'created_at' => $testCase->created_at,
                );
        }
        return $testCaseList;
    }
    
    public static function addVisiualEffectOnPR($passRate)
    {
        return self::addBenchmarkColouring($passRate);
    }
    
    private static function addBenchmarkColouring($benchmarkRate)
    {
        if(intval($benchmarkRate) < 50)
        {
            $benchmarkRate = '<span style="color:red;font-weight:bolder;">'.$benchmarkRate.'</span>';
        }
        else if(intval($benchmarkRate) >= 50 && intval($benchmarkRate) < 80)
        {
            $benchmarkRate = '<span style="color:#E68A00;">'.$benchmarkRate.'</span>';
        }
        else
        {
            $benchmarkRate = '<span style="color:green;">'.$benchmarkRate.'</span>';
        }
        return $benchmarkRate;
    }
    
    
    public static function processToReadableWithBrowserIcon($browser)
    {
        $output = '';
        switch ($browser)
        {
            case 'FIREFOX':
                $output = 'Mozilla Firefox &nbsp;&nbsp;'.theme('image', array(
                                    'path' => drupal_get_path('module', 'reportsuite').
                                              '/lib/firefox_icon.gif',
                                    'alt' => 'ffLogo',
                                    'title' => 'Firefox',
                                    'width' => '4%',
                                    'height' => '4%',
                                    ));
                break;
            
            case 'CHROME':
                $output = 'Google Chrome &nbsp;&nbsp;'.theme('image', array(
                                    'path' => drupal_get_path('module', 'reportsuite').
                                              '/lib/google_chrome_logo.jpg',
                                    'alt' => 'gclogo',
                                    'title' => 'Chrome',
                                    'width' => '4%',
                                    'height' => '4%',
                                    ));
                break;
            
            case 'MSIE':
                $output = 'Microsoft IE &nbsp;&nbsp;'.theme('image', array(
                                    'path' => drupal_get_path('module', 'reportsuite').
                                              '/lib/msie-icon.jpg',
                                    'alt' => 'msielogo',
                                    'title' => 'MSIE',
                                    'width' => '4%',
                                    'height' => '4%',
                                    ));
                break;
        }
        return $output;
    }
    
    public static function parseDurationVariable($duration)
    {
        $timespan = array();
            if(!empty($duration)){
                $timespan['from'] = $duration['from'];
                $timespan['to'] = $duration['to'];
            }else{
                $timespan['from'] = date('Y-m-d', strtotime("Monday"));
                $timespan['to'] = date('Y-m-d', strtotime("Sunday"));
            }
            return $timespan;
    }
    
    /**
     * method to create releasephases
     * 
     * @param type $pageQ => ?q=reportsuite/bla/bla..
     * @param type $tpid
     * @return type
     */
    public static function releasePhaseArrLinks($pageQ,$tpid)
    {
        $releasePhases = reportsuitequeries::getAllReleasePhasesByTpid($tpid);
        $links = array();
        foreach($releasePhases as $rp){$links[] = l($rp, $pageQ.'/'.$rp);}
        return $links;
    }
    
    /**
     * Table Data Structure
     * 
     * @param type $testResults
     */
    public static function testResultsIntoTDS($testResults)
    {
        $tr = array();
        $tr['passed'] = isset($testResults['Passed'])? intval($testResults['Passed']):0;
        $tr['warning'] = isset($testResults['Warning'])? intval($testResults['Warning']):0;
        $tr['failed'] = isset($testResults['Failed'])? intval($testResults['Failed']):0;
        $tr['error'] = isset($testResults['TAFError'])? intval($testResults['TAFError']):0;
        $tr['totalTestCases'] = $tr['passed'] + $tr['warning'] + $tr['failed'] + $tr['error'];
        
        return $tr;
    }
    
    /**
     * 
     * 
     * @param type $tpid
     * @return array name, jenkinsHtmlReport, scriptPrefix
     */
    public static function customerAlias($tpid, $all = false)
    {
        $customerAlias = array(
                           1 => array('name' => 'Malaysia Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_MAS_PST_ATL_Automation/'),
                           2 => array('name' => 'Air China - Unused',
                                      'jenkinsJob' => '',
                                      'disabled' => true,),
                           3 => array('name' => 'WestJet - Unused',
                                      'jenkinsJob' => '',
                                      'disabled' => true,),
                           4 => array('name' => 'Air Pacific',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_AirPacific_PST_ATL_Automation/'),
            
                           5 => array('name' => 'Frontier Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Frontier_PST_ATL_Automation/',
                                      'scriptPrefix' => 'Frontier',
                                      'disabled' => true,
                                     ),
                           6 => array('name' => 'Copa Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_COPA_PST_ATL_Automation/',
                                      'scriptPrefix' => 'CopaAirlines'
                                     ), 
                           7 => array('name' => 'SmartWings',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_SmartWings_PST_ATL_Automation/',
                                        'disabled' => true,
                                        ),
                           8 => array('name' => 'Philippine Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_PAL_PST_ATL_Automation/'),
                           9 => array('name' => 'Air Malta',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_AirMalta_PST_ATL_Automation/',
                                    ),
                           10 => array('name' => 'South Africa Airways',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_SAA_PST_ATL_Automation/',
                                      'disabled' => true,
                                      ),
                           11 => array('name' => 'WestJet',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_WJA_PST_ATL_Automation/'),
                           12 => array('name' => 'Delta Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Delta_PST_ATL_Automation/',
                                      'scriptPrefix' => 'Delta',
                                      'disabled' => true,
                                       ),
                           13 => array('name' => 'Garuda Indonesia',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Garuda_Indonesia_PST_ATL_Automation/',
                                      'disabled' => true,
                                      ),
                           14 => array('name' => 'Azerbaijan Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Azerbaijan_PST_ATL_Automation/',
                                      'scriptPrefix' => 'Azerbaijan',
                                      'disabled' => true,
                                      ),
            
                           15 => array('name' => 'Air Botswana',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Botswana_PST_ATL_Automation/',
                                        'disabled' => true,
                                        ) ,
            
                           16 => array('name' => 'Georgian Airways',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Georgian_Airways_PST_ATL_Automation/',
                                      'disabled' => true,
                                       ),
            
                           17 => array('name' => 'Berjaya Air',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_BVT_PST_ATL_Automation/',
                                      'scriptPrefix' => 'AirBerjaya',
                                      'disabled' => true,
                                      ),
                           18 => array('name' => 'STA',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_STA_PST_ATL_Automation/',
                                      'scriptPrefix' => 'STA',
                                      ),
            
                           19 => array('name' => 'Air China',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_CCA_PST_ATL_Automation/'),
            
                           20 => array('name' => 'Oman Air',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_OmanAir_PST_ATL_Automation/',
                                      'disabled' => true),
                           23 => '',
                           24 => '',
                           25 => '',
                           26 => '',
                           27 => array('name' => 'Air India',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_AirIndia_PST_ATL_Automation/',
                                      'disabled' => true,
                                        ),
                           29 => array('name' => 'Copa REST',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_COPA_REST_PST_ATL_Automation/'),
                           30 => array('name' => 'FlyUIA',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_UIA_PST_ATL_Automation/',
                                      'scriptPrefix' => 'FlyUIA',
                                      'disabled' => true,
                                        ),
                           31 => array('name' => 'TAME',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_TAME_PST_ATL_Automation/',
                                      'disabled' => true,
                                    ),
                           32 => array('name' => 'Iran Air',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_IranAir_PST_ATL_Automation/',
                                      'disabled' => true,
                                    ),
                           33 => array('name' => 'Yakutia Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Yakutia_PST_ATL_Automation/',
                                      'disabled' => true,
                                      ),
                           34 => array('name' => 'HPTrips',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_HP_PST_ATL_Automation/',
                                      'disabled' => true,
                                      ),
                           35 => array('name' => 'System Test Portal',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/PSTAutomation_SystemTest/',
                                      'scriptPrefix' => 'SystemTest',),
                           36 => '',
                           37 => array('name' => 'Silver System Test',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/SILVER_SYSTEM_TESTS_PSTAUTOMATION/',
                                      'scriptPrefix' => 'SilverSystemTest',),
                           38 => array('name' => 'Brussels Airlines',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/view/PST%20Deployments/job/TDP_Brussels_PST_ATL_Automation/',
                                      'scriptPrefix' => 'BrusselsAirlines',),
                          39 => array('name' => 'WestJet REST',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_WJA_REST_PST_ATL_Automation/',
                                      'disabled' => true,
                                      ),
                          40 => array('name' => 'Product',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Product_PST_ATL_Automation/',
                                      ),
                          41 => array('name' => 'Air Transat',
                                      'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_TSC_PST_ATL_Automation/',
                                      ),
                          42=> array('name' => 'JetBlue', 'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_JetBlue_PST_ATL_Automation/',
					),
                          43=> array('name' => 'AerLingus', 'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_AerLingus_PST_ATL_Automation/',
					),
                          44=> array('name' => 'Abacus', 'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Abacus_PST_ATL_Automation/',
                                        ),
                          45=> array('name' => 'PCI System Test', 'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_PCI_SYSTEM_TEST_Automation/',
                                        ),
                          47=> array('name' => 'Edelweiss Air', 
                                     'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_EDW_PST_ATL_Automation/',
                                     'scriptPrefix' => 'EdelweissAir'   ),
            
                          48=> array('name' => 'Virgin Atlantic', 
                                     'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_VIR_PST_ATL_Automation/',
                                     'scriptPrefix' => 'VirginAtlantic'   ),
                          49=> array('name' => 'JetBlue REST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_JBU_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'JetBlueREST' ),
                         50=> array('name' => 'AirChina REST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_CCA_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'AirChinaREST' ),
                        52=> array('name' => 'JetBlue REST SOAPUI',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_JBU_REST_SOAPUI_PST_ATL_Automation/',
                                        'scriptPrefix' => 'JetBlueRESTSOAPUI'),
			53=> array('name' => 'Swiss Air',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_SWR_PST_ATL_Automation/',
                                        'scriptPrefix' => 'SwissAir'),
			54=> array('name' => 'West Air',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_CHB_PST_ATL_Automation/',
                                        'scriptPrefix' => 'WestAir'),
			55=> array('name' => 'Air Malta REST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_AirMalta_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'AirMaltaREST'),
			57=> array('name' => 'BrusselsAirlines REST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_BrusselsAirlines_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'BrusselsAirlinesREST'),

                        54=> array('name' => 'West Air',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_CHB_PST_ATL_Automation/',
                                        'scriptPrefix' => 'WestAir'),
                        58=> array('name' => 'SwissAir SOAPUI',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_SWR_SOAPUI_PST_ATL_Automation/',
                                        'scriptPrefix' => 'SwissAirSOAPUI'),
                        59=> array('name' => 'AerLingus SOAPUI',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_EIN_SOAPUI_PST_ATL_Automation/',
                                        'scriptPrefix' => 'AerLingusSOAPUI'),
						60=> array('name' => 'BeibuGX',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_BeibuGX_PST_ATL_Automation/',
                                        'scriptPrefix' => 'BeibuGX'),
						61=> array('name' => 'ProductREST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_Product_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'ProductREST'),
						65=> array('name' => 'EdelweissAirREST',
                                        'jenkinsJob' => 'http://jenkins.datalex.ie/jenkins/job/TDP_EDW_REST_PST_ATL_Automation/',
                                        'scriptPrefix' => 'EdelweissAirREST'),



                    );
    
    
        if($all)
            return $customerAlias;
        else
            return $customerAlias[$tpid];
    }
    
}

?>
