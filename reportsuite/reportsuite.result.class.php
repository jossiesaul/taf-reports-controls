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
class reportsuiteresult {
    //put your code here
    
    public static function getResutStatusCriteria()
    {
        return array('Passed', 'Warning', 'Failed');
    }
    
    public static function generateResultReportFromResultObject($xmlObj, $additionalData)
    {
        $resultReport = array();
        $testCasesResultData = self::sortTestCasesResultDataByScenario(
                $additionalData['testResData']);
        foreach ($xmlObj->testScenarios->TestScenario as $scenario)
        {
            $resultReport[(String)$scenario->name]['summary'] = 
                    self::generateSummaryPerResultScenarioObject(
                            $scenario);
            $resultReport[(String)$scenario->name]['table'] =
                    self::generateTablePerResultScenarioObject(
                            $scenario, $additionalData);
            $resultReport[(String)$scenario->name]['detail'] = 
                    $testCasesResultData[(String)$scenario->name];
            
        }
        return $resultReport;
    }
    
    
    public static function generateSummaryPerResultScenarioObject($scenarioData)
    {
        return array('TotalTestCases' => $scenarioData->totalTestCases,
                                                   'Passed' => $scenarioData->passed,
                                                   'Failed' => $scenarioData->failed,
                                                   'Warning' => $scenarioData->warning,
                                                   'NotExecuted' => $scenarioData->notExecuted,
                                                   'PassRate' => reportsuitequeries::calculatePassRate(
                                                                            $scenarioData->totalTestCases,
                                                                            $scenarioData->passed + $scenarioData->warning,
                                                                            true),
            );
    }
    
    public static function generateTablePerResultScenarioObject($scenario, $additionalData)
    {
        $reid = $additionalData['reid'];
        $runDetail = $additionalData['runDetail'];
        $testResData = $additionalData['testResData'];
        $execListData = $additionalData['execListData'];
        $screenshots = $additionalData['screenshots'];
       // drupal_set_message('<pre>' . print_r($execListData, 1) . '</pre>');
        $renderedTables = array();
        
            $headers = array('TestCase ID', 'Outcome', 'Duration', 'Details', 'Screen shots', '');
            $rows = array();

            foreach ($scenario->testCases->TestCase as $testCase)
            {
                $tcOutcome = '';
                $details = '';
                $tcRowBgOverrideFlag = false;
                $itemsAddOn = '';
                $rerData = '';
                
                if(isset($testCase->reRunData))
                    $rerData = reportsuiteXmlProcessor::gatherRerunOnCase($reid,$testCase);
                
                if(!empty($rerData))
                {
                    $rerImgPath = drupal_get_path('module', 'reportsuite').'/lib/rerun.png';
                    $rrImg = '&nbsp;&nbsp;&nbsp;<img src="'.$rerImgPath.'" width="20" height="20"/>';
                   
                    //override
                    $testCase = self::overrideTestCaseByRerun($testCase, $rerData);
                    $rerunPatch = true;
                    
                     $itemsAddOn .= popup(array('title' => $rrImg,
                                    'text' => self::caseRunHistory($testCase->id, $rerData),
                                    'origin' => 'top-right',  
                                    'expand' => 'bottom-right',
                                    'width' => 450
                         ));
                }
                
                //will override everything even rerun
                if (isset($testCase->reportSuiteAddit->overrideTcOutcomeFlag) &&
                   $testCase->reportSuiteAddit->overrideTcOutcomeFlag == TRUE)
                {
                    $tcRowBgOverrideFlag = TRUE;
                    $tcOutcome = $testCase->reportSuiteAddit->overrideTcOutcomeValue;
                    $details .= '<div style="width:400px;">';
                    $details .= '<u><h5>Override Outcome Reason:</h5></u>';
                    $details .= $testCase->reportSuiteAddit->overrideTcOutcomeReason;
                    $details .= '</div>';
                    $itemsAddOn .= '&nbsp;&nbsp;&nbsp;<img src="sites/default/files/red-flag.png" width="25" height="25"/>';
                }
                else
                {
                    $tcRowBgOverrideFlag = FALSE;
                    $tcOutcome = $testCase->status;
                    $details = str_replace("::", "<br /><br />", $testCase->details);
                }
                
                if(($frdata = reportsuiteXmlProcessor::getFailureReason($reid,
                        array('scenario' => (String)$scenario->name,
                              'testCaseId' => (String)$testCase->id))) !== FALSE)
                {
                    $details .= '<br /><br />';
                    $details .= '<div style="border:2px dashed red;text-align:center; width:400px;">
                                == <b>Failure Reason</b> == <br /><u>'.
                                $frdata['failureReason'].'</u> - '.
                                $frdata['failureReasonComment'].
                                '<br /><br />
                                </div>';
                    $itemsAddOn .= $additionalData['failureReasonIcon'];
                }

                if(!empty($testCase->reservationNumber))
                    $details .= '<b>Reservation/PNR: </b> '.$testCase->reservationNumber;
                
                
                $screenshotsLinks = '';
                $si = 0;
                if(!empty($screenshots))
                {
                    foreach($screenshots as $ss)
                    {
                        if(!empty($ss['filename']))
                        {
                          //This hard coded integer is formulated from 10 digits of unix timestamp and 5 additional
                          //plus another 3 digit due to unkown reason....
                          //characters to remove from the suffix of the filename to match the test case id
                          //however this hard code will only last until the year 2268 AD - I'll be long dead by then
                         $filenameProcessed = substr($ss['filename'], 0, -18);
                            if($testCase->id == $filenameProcessed)
                            {
                              $si++;
                              $screenshotsLinks .= l('Screen#'.$si, 'reportsuite/rrdScreenshots/'.$ss['resid'].'/null/null',
                                      array('attributes' => 
                                          array('rel' => "lightframe[$testCase->id|width:1000px; height:1000px;][$si]"))).'<br />';
                            }
                        }
                    }
                }

                $toolLinks = '';
                $toolLinks = l('- Override Outcome -', "reportsuite/overrideOutcome/".$reid."/".$testCase->id,
                                        array('query' => array(drupal_get_destination())));
//                $toolLinks .= '<a id="oo_'.$testCase->id.'_link">- Override Outcome -</a>';
                $toolLinks .= '<br />';
                $toolLinks .= '<a id="fr_'.$testCase->id.'_link">- Failure Reason -</a>';

               $actionTools = popup(array('title' => '<img src="sites/default/files/barretr_Pencil.png" width="25" height="25"/>',
                                    'text' => $toolLinks,
                                    'origin' => 'top-right',  
                                    'expand' => 'bottom-right'));

               $testCaseId = (String)$testCase->id;
               
               ///////////////////////////
               /// Execution LIST pop up
               if($runDetail['testType'] != 'SERVICE')
               {
                if(!empty($execListData[(String)$scenario->name][(String)$testCase->id]))
                {
                 $execListDataDisplay = reportsuite::processUIFriendlyExecutionListByTestCase(
                                          $execListData[(String)$scenario->name][(String)$testCase->id],
                                          (String)$testCase->id,
                                          $testResData);

                 $testCaseId = popup(array('title' => (String)$testCase->id,
                                      'text' => $execListDataDisplay,
                                      'origin' => 'top-right',  
                                      'expand' => 'bottom-right',
                                      'width' => 750,
                                      'activate' => 'click'
                     ));
                }
               }
               $rows_values = array($testCaseId.' '.$itemsAddOn.'<br />'.
                                '<span style="font-size:10px;">'.
                                $testCase->description.
                                '</span>',
                                $tcOutcome,
                                reportsuite::parseHHMMSSDuration($testCase->executionTime),
                                reportsuite::processDetailMessageFromRunResults($details),
                                $screenshotsLinks,
                                $actionTools,
                          );


                if ($tcRowBgOverrideFlag)
                {
                    $rows[] = array('data' => $rows_values,
                                    'style' => self::determineStatusBgColouring(
                                            $testCase->reportSuiteAddit->overrideTcOutcomeValue),
                              );
                }
                else
                {
                    $rows[] = array('data' => $rows_values,
                                    'style' => self::determineStatusBgColouring(
                                            $testCase->status),
                              );
                }
            }
             
        return theme('table', array ('header' => $headers, 'rows' => $rows, array('class' => 'runResultDetailTable')));
    } 
   
    private static function overrideTestCaseByRerun($testCase, $rerData)
    {
       $testCase->executionTime = $rerData['latest']['executionTime'];
       $testCase->status = $rerData['latest']['status'];
       $testCase->details = $rerData['latest']['details'];
       $testCase->comment = $rerData['latest']['comment'];
       if(isset($rerData['latest']['reservationNumber']))
            $testCase->reservationNumber = $rerData['latest']['reservationNumber'];
                    
       return $testCase;
    }
    
    public static function caseRunHistory($testCaseId, $rerData)
    {
        $table ='';
        $table .= '<table>';
        
        $table .= '<tr style="2.5px solid black;'.
                reportsuite::getPassFailWarningCSSStyle($rerData['latest']['status']).';">';
        $table .= '<td>';
        $table .= '<center><h3><b>Current | Status: '. $rerData['latest']['status'].'</b></h3></center>';
        $table .= '<br />';
        $table .= '<b>Ran On:</b> '.$rerData['latest']['rerunDate'];
        $table .= '<br />';
        $table .= '<b>Recorded REID:</b> '.$rerData['latest']['reid'];
        $table .= '</td>';
        $table .= '</tr>';
        
        $table .= '<tr style="2.5px solid black;'.
                reportsuite::getPassFailWarningCSSStyle($rerData['MASTER']['status']).';">';
        $table .= '<td>';
        $table .= '<center><h3><b>Original | Status: '. $rerData['MASTER']['status'].'</b></h3></center>';
        $table .= '<br />';
        $table .= '<b>Time to Execute:</b> '.$rerData['MASTER']['executionTime'];
        $table .= '<br />';
        $table .= '<b>Fail Severity:</b> '.$rerData['MASTER']['comment'];
        $table .= '<br />';
        $table .= '<b>Details:</b> '.$rerData['MASTER']['details'];
        $table .= '</td>';
        $table .= '</tr>';
        
        if(isset($rerData['record']))
        foreach($rerData['record'] as $rdata)
        {
            $table .= '<tr style="2.5pt solid black;'.
                reportsuite::getPassFailWarningCSSStyle($rdata['status']).';">';
            $table .= '<td>';
            $table .= '<center><h3><b>Record-'.$rdata['rreid'].' | Status: '. $rdata['status'].'</b></h3></center>';
            $table .= '<br />';
            $table .= '<b>Ran On:</b> '.$rdata['rerunDate'];
            $table .= '<br />';
            $table .= '<b>Recorded REID:</b> '.$rdata['reid'];
            $table .= '<br />';
            $table .= '<b>Time to Execute:</b> '.$rdata['executionTime'];
            $table .= '<br />';
            $table .= '<b>Fail Severity:</b> '.$rdata['comment'];
            $table .= '<br />';
            $table .= '<b>Details:</b> '.$rdata['details'];
            $table .= '</td>';
            $table .= '</tr>';
        }
        
        $table .= '</table>';
        
        return $table;
    }
    
    protected static function determineStatusBgColouring($status)
    {
        if($status == "Failed")
        {
             $rowColouring = 'background-color:#FFB2B2;';
        }
        else if($status == "Passed")
        {
             $rowColouring = 'background-color:#B2FFB2;';
        }
        else if($status == "Warning")
        {
             $rowColouring = 'background-color:#FFEB99;';
        }
        else if($status == "Error")
        {
             $rowColouring = 'background-color:#D0D0D0;';
        }
        return $rowColouring;
    }
    
    public static function getCommonFailureReason()
    {
        return array(
            'FAIL' => drupal_map_assoc(array(
                'Maintainance','Environment Down','Not Flights Available',
                'Itinenary Page Issue','Fares Issue','Payment Page Issue',
                'Travellers Page issue','Seat Select Page issue','Other',
                )),
            'ERROR' => drupal_map_assoc(array(
                'Identified TAF Error'
                )),
            );
        
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
    
    
    public static function exportTestResults($format, $testResults, $tpid, $runBy, $testPath){
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
//        drupal_set_message($format);
//        drupal_set_message($tpid);
//        drupal_set_message($reid);
//        drupal_set_message($testPath);
//        drupal_set_message('<pre>' . print_r($testResults, 1) . '</pre>');
        $customerAlias = reportsuite::customerAlias($tpid);
        
        
        if($format == "excel"){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()
                    ->setCreator("PST Automation");
            $objPHPExcel->getProperties()
                    ->setLastModifiedBy("PST Automation");
            $objPHPExcel->getProperties()
                    ->setTitle("Automation Test Results");
            
            $i = 1;
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, 'ID');
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, 'RESULT');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, 'NOTES');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, 'STAFFTIME');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, 'USER');
            $result = 'unresolved';
            foreach($testResults as $testCaseId => $data){
                if($testCaseId !== 'uuid'){
                    
                $m = explode(":", $data['duration']);
                $minutes = $m[1];
                $i++;
                $result = in_array($data['outcome'], 
                        array('Passed', 'Warning'))?'pass':'fail';
                
//                drupal_set_message('<pre>' . print_r($data, 1) . '</pre>');
                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $testPath.$testCaseId);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $result);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $data['failDets']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $minutes);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, 'automationuser@datalex.com');
                
                }

            }
                $objPHPExcel->setActiveSheetIndex(0);
                $i++;
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, 'dummyCase');
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, 'pass');
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, '');
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, '1');
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, 'dummyuser');
            $objPHPExcel->getActiveSheet()->setTitle('TestResults');
            

            
            $filename = drupal_get_path('module', 'reportsuite').
                        '/lib/testResultsExports/'.
                        $customerAlias['name'];
//            
//            $filename = $customerAlias['name'];
//            
////            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
////            $objWriter->save($filename.'.xlsx');
            $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
            $objWriter->save($filename.'.xls');
            
            drupal_goto('http://10.153.30.100/'.$filename.'.xls');
         
            
        }else if ($format == "csv"){
            
        }
    }
}

?>
