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
class reportsuitequeries {
    
    public static $automationResultsStore = array('Total' => 0,
                                                  'Pass' => 0,
                                                  'Warning' => 0,
                                                  'Fail' => 0,
                                            );
    
    /**
     * Get All tpid in taf project table
     * 
     * @return Array Containg all fields from taf_project table
     */
    public static function getAllTpid()
    {
        $projectList = array();
        
        db_set_active('taf');
            $query = db_select('taf_projects', 'tp');
            $resultSet = $query->fields('tp')->execute();
        db_set_active();      
       
       foreach ($resultSet as $result)
       {
           $customerAlias = reportsuite::customerAlias($result->tpid);
           if(!empty($customerAlias) && 
             (!isset($customerAlias['disabled']) || $customerAlias['disabled'] === false))
           $projectList[$result->tpid] = array('tpid' =>$result->tpid,
                                             'Customer' => $result->customer,
                                             'CustomerAlias' => $customerAlias['name'],
                                             'CustomerCode' => $result->customer_code,
                                             'POS' => $result->pos,
                                                            );
       }
        return $projectList;
    }
    
    /**
     * Get Tpid using Reid
     * 
     * @param int $reid 
     * @return int tpid
     */
    public static function getTpidByReid($reid)
    {
       db_set_active('taf');
            $query = db_select('taf_projects', 'tp');
            $query->join('run_execution', 're', 're.tpid = tp.tpid');
            $query->fields('tp', array('tpid'))
                  ->condition('re.reid', $reid)
                  ->range(0,1);

            $result = $query->execute()->fetchField();
       db_set_active();      
       return $result;
    }
    
    /**
     * Get Tpid by projectname and Pos
     * 
     * @param String $projectName
     * @param String $pos
     * @return int tpid
     */
    public static function getTpidByCustomerAndPos($projectName, $pos)
    {
        db_set_active('taf');
            $query = db_select('taf_projects', 'tp');
            $query->fields('tp', array('tpid'))
                  ->condition('tp.customer', $projectName)
                  ->condition('tp.pos', $pos)
                  ->range(0,1);
            $result = $query->execute()->fetchField();
       db_set_active();      
       return $result;
    }
    
    /**
     * Method to get TAF Project Details by tpid
     * 
     * @param type $tpid
     * @return object all fields from taf project
     */
    public static function getTpbyTpid($tpid)
    {
        db_set_active('taf');
            $query = db_select('taf_projects', 'tp');
            $query->fields('tp')
                  ->condition('tp.tpid', $tpid);
            $result = $query->execute()->fetchObject();
        db_set_active();      
        return $result;
    }
    
    /**
     * get project Test Case Data from teet_cases table by TestCaseID 
     * 
     * @param type $testCaseID
     * @param type $tpid
     * @return object test case details
     */
    public static function getTestCaseByTestCaseId($testCaseID, $tpid)
    {
        db_set_active('taf');
            $query = db_select('test_cases', 'tc');
            $query->fields('tc')
                  ->condition('tc.testcase_id', $testCaseID)
                  ->condition('tc.tpid', $tpid)
                  ->range(0,1);
            $result = $query->execute()->fetchObject();
       db_set_active();      
       
       return $result;
    }
    
    /**
     * get project Test Cases from test case table by tpid
     * 
     * @param type $tpid
     * @return object test cases list details
     */
    public static function getTestCasesByTpid($tpid)
    {
        db_set_active('taf');
            $query = db_select('test_cases', 'tc');
            $query->fields('tc')
                  ->condition('tc.tpid', $tpid);
            $resultSet = $query->execute();
       db_set_active();      
       
       return $resultSet;
    }
    
    
    /**
     * get project test cases by Date 
     * 
     * @param int $tpid
     * @param array $dates[from] $dates[to]
     * @return type
     */
    public static function getTestCasesByDate($tpid, $dates = array())
    {
        db_set_active('taf');
            $query = db_select('test_cases', 'tc');
            $query->fields('tc')
                  ->condition('tc.tpid', $tpid)
                  ->condition('tc.created_at', 
                              array($dates['from'], $dates['to']),
                              'BETWEEN'
                             );
            $resultSet = $query->execute();
        db_set_active();      
       
       return $resultSet;
    }
    
    
    public static function getReidNearActualReid($reid, $tpid, $dept, $direction)
    {
        
        $operator = $direction === 'next'? '>':'<';
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
              ->condition('re.reid', $reid, $operator)
              ->condition('re.dept', $dept)
              ->condition('re.locally_executed', 0)
              ->condition('re.run_flag', 1, '!=')
              ->condition('re.run_flag', 69, '!=')
              ->fields('re', array('reid'))
              ->range(0, 1);
        
        if($direction === 'previous')
           $query->orderBy('reid', 'DESC');
        
        $result = $query->execute()->fetchField();
        
        db_set_active();
        
        return $result;
    }
    
    /**
     * Inset new test cases by Test cases ID
     * 
     * @param type $testCaseID
     * @param type $tpid
     * @param type $data
     * @param type $multiple
     */
    public static function setTestCaseByTestCaseId($testCaseID, $tpid, $data, $multiple = false)
    {
            db_set_active('taf');
            $query = db_insert('test_cases')->fields(array(
                                'tpid', 'scenario', 'testcase_id', 'description', 'num_of_actionsteps', 'created_at'));
                if($multiple)
                {
                    foreach($data as $sd)
                    {   
                        $query->values(array('tpid' => $tpid,
                                            'scenario' => $sd['scenario'],
                                            'testcase_id' => $sd['testCaseId'],
                                            'description' => $sd['description'],
                                            'num_of_actionsteps' => $sd['numOfActionSteps'],
                                            'created_at' => date('Y-m-d h:m:s'),
                                    ));
                    }
                }
                else
                {
                    $query->values(array('tpid' => $tpid,
                                        'scenario' => $sd['scenario'],
                                        'testcase_id' => $testCaseID,
                                        'description' => $sd['description'],
                                        'num_of_actionsteps' =>$sd['numOfActionSteps'],
                                        'created_at' => date('Y-m-d h:m:s'),
                                        ));
                }
             $query->execute();
            db_set_active();
    }
    
    /**
     * Method To update TestCase Details.
     * 
     * <b>THIS METHOD CAN ONLY BE CALLED PRIOR TO PRIOTORY CONDITIONS</b>
     * 
     * @param type $testCaseID
     * @param type $tpid
     * @param array $toUpdateData -> the key of this particular array variable should represent <br />
     *                                                            to that of the table fieldname
     */
    public static function updateTestCaseByTestCaseId($testCaseID, $tpid, $toUpdateData = array())
    {
        
        db_set_active('taf');
            $query = db_update('test_cases');
            $query->fields($toUpdateData)
                        ->condition('testcase_id', $testCaseID, '=')
                        ->condition('tpid', $tpid, '=')
                        ->execute();
        db_set_active();
        
    }
    
    /**
     * Get the Last reid by tpid and deptarment
     * 
     * @param type $tpid
     * @param type $dept
     * @return type
     */
    public static function getLastReidByTpidAndDept($tpid, $dept)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
//        $query->condition('re.tpid', $tpid)
//              ->condition('re.dept', $dept)
//              ->condition('re.run_duration', 25, '>')
//              ->condition('re.locally_executed', 0)
//              ->condition('re.run_flag', 0)
        $query->condition('re.tpid', $tpid)
              ->condition('re.dept', $dept)
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')  
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode = 'SERVICE') OR ".
                      "(re.run_duration > 20 AND re.run_mode IN('UI', 'GRID', 'SOAPUI'))")
              ->fields('re', array('reid'))
              ->orderBy('reid', 'DESC')
              ->range(0,1);
        $result = $query->execute()->fetchField();
        db_set_active();
        
        return $result;
    }
    
    /**
     * get <b>ALL</b> REID by tpid and department
     * 
     * @param type $tpid
     * @param type $dept
     * @param type $limit
     * @return type
     */
    public static function getAllReidByTpidAndDept($tpid, $dept, $limit = 0)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
              ->condition('re.dept', $dept)
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode = 'SERVICE') OR ".
                      "(re.run_duration > 20 AND re.run_mode IN('UI', 'GRID', 'SOAPUI'))")
              ->fields('re');
        
        if ($limit > 0)
        {
            $query->range(0, $limit)->orderBy('re.reid', 'DESC');
        }
        
        $result = $query->execute();
        db_set_active();
        
        return $result;
    }

    public static function getAllReidByTpidAndReleaseStage($tpid, $releaseStage, $limit = 0)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
              ->condition('re.release_stage', $releaseStage)
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode = 'SERVICE') OR ".
                      "(re.run_duration > 20 AND re.run_mode IN('UI', 'GRID', 'SOAPUI'))")
              ->fields('re');
        
        if ($limit > 0)
        {
            $query->range(0, $limit)->orderBy('re.reid', 'DESC');
        }
        
        $result = $query->execute();
        db_set_active();
        
        return $result;
    }
    
    public static function getAllReidByTpid($tpid, $limit = 0)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode = 'SERVICE') OR ".
                      "(re.run_duration > 20 AND re.run_mode IN('UI', 'GRID', 'SOAPUI'))")
              ->fields('re');
        
        if ($limit > 0)
        {
            $query->range(0, $limit)->orderBy('re.reid', 'DESC');
        }
        
        $result = $query->execute();
        db_set_active();
        
        return $result;
    }
    
    /**
     * Grab All the Run Execution ID by the TPID and department on time span
     * 
     * @param type $tpid
     * @param type $dept
     * @param type $timespan
     * @return type
     */
    public static function getAllReidByTpidAndDeptOnTimeSpan($tpid, $dept, $timespan)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
                ->condition('re.dept', $dept)
                ->condition('re.run_duration', 15, '>')
                ->condition('re.locally_executed', 0)
                ->condition('re.run_flag', 1, '!=')
                ->condition('re.run_flag', 69, '!=')
                ->condition('re.run_datetime_start', array($timespan['from'], 
                            $timespan['to']), 'BETWEEN')
                ->fields('re');
       
        $result = $query->execute();
        db_set_active();
        
        return $result;
    }
    
    /**
     *  Method to get All Run Result Details by a list of Run Execution ID 
     * @param type $reid
     * @return type
     */
    public static function getReidByCollection($reidCollection)
    {
          if(is_object($reidCollection))
          {
              $reidArr = array();
              foreach($reidCollection as $res)
              {
                  array_push($reidArr, $res->reid);
              }
              $reidCollection = $reidArr;              
          }
           db_set_active('taf');
           $query = db_select('run_execution', 're');
           $query->condition('re.reid', $reidCollection, 'IN')->fields('re');
           
           $resultSet = $query->execute();
           db_set_active();
           
           return $resultSet;
    }
    
    /**
     * Method to get all run result detail by a single Run Execution ID
     * 
     * @param type $reid
     * @return type
     */
    public static function getAllRunResultsInfoByReid($reid)
    {
        db_set_active('taf');
           $query = db_select('run_results', 'rr');
           $query->condition('rr.reid', $reid)
                 ->fields('rr');
           $resultSet = $query->execute();
           db_set_active();
           
           return self::processAutomationResultOnRunResultsData($reid, $resultSet);           
    }
    
    /**
     *  Method to get All Run Result Details by a list of Run Execution ID 
     * @param type $reid
     * @return type
     */
    public static function getAllRunResultFromListOfReid($reid = array())
    {
           db_set_active('taf');
           $query = db_select('run_results', 'rr');
           $query->condition('rr.reid', $reid, 'IN')
                 ->fields('rr');
           $resultSet = $query->execute();
           db_set_active();
           $automationResultStore = array();
          
           return self::processAutomationResultOnRunResultsData(null,  $resultSet);
           
    }
    
    
    /**
     * Get the Last run Date and Time
     * 
     * @param type $tpid
     * @param type $dept
     * @return type
     */
    public static function lastRunDateTime($tpid, $dept)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.tpid', $tpid)
              ->condition('re.dept', $dept)
              ->condition('re.run_duration', 25, '>')
              ->condition('re.locally_executed', 0)
              ->condition('re.run_flag', 1, '!=')
              ->condition('re.run_flag', 69, '!=')
              ->fields('re', array('run_datetime_stop'))
              ->orderBy('reid', 'DESC')
              ->range(0,1);
        db_set_active();
        
        return $query->execute()->fetchField();
    }
    
    /**
     * Method to compile all run result data from run_result table using
     * reid and process into automationResultStore 
     * 
     * @param type $reid
     * @return type
     */
    public static function getRunExecutionResultsSummaryByReid($reid)
    {
           db_set_active('taf');
           $query = db_select('run_results', 'rr');
           $query->condition('rr.reid', $reid)
                 ->fields('rr', array('total_test_cases', 'total_pass', 'total_warning', 'total_fail'));
           $resultSet = $query->execute();
           db_set_active();
           
           $autoResultsStore = array('Total' => 0,
                                    'Pass' => 0,
                                    'Warning' => 0,
                                    'Fail' => 0,
                                    'TAFError' => 0,
                              );
           $autoResultsStore['TAFError'] += reportsuiteXmlProcessor::getSpecifiedOverallResult(
                                           reportsuiteXmlProcessor::processTestResultsXml($reid), 
                                           'tafError');
           foreach ($resultSet as $result)
           {
                if(!empty($result))
                {
                  $autoResultsStore['Total'] = $autoResultsStore['Total'] + $result->total_test_cases;
                  $autoResultsStore['Pass'] = $autoResultsStore['Pass'] + $result->total_pass;
                  $autoResultsStore['Warning'] = $autoResultsStore['Warning'] + $result->total_warning;
                  $autoResultsStore['Fail'] = $autoResultsStore['Fail'] + $result->total_fail;
                }
           }
           
           return $autoResultsStore;
    }
    
    /**
     * Run Execution list using $reid
     * 
     * @param type $reid
     * @return type
     */
    public static function getRunExecutionList($reid)
    {
           db_set_active('taf');
           $query = db_select('run_executionlist_storage', 'rels');
           $query->condition('rels.reid', $reid)
                 ->fields('rels', array('executionlist'));
           $result = $query->execute()->fetchField();
           db_set_active();
           
           return $result;
    }


    /**
     * get run execution list Details by reid
     * 
     * @param type $reid
     * @return type
     */
    public static function getRunExecutionDetailsByReid($reid)
    {
        
        db_set_active('taf');
            $query = db_select('run_execution', 're');
            $query->condition('re.reid', $reid)
                  ->fields('re')
                  ->range(0,1);
            $resultsObj = $query->execute()->fetchObject();
        db_set_active();
        
        return $resultsObj;
    }
    
    /**
     * get Run execution on specified field and reid
     * 
     * @param type $reid
     * @param type $field
     * @return type
     */
    public static function getRunExecutionFieldQueryByReid($reid, $field)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.reid', $reid)
              //Dept not need because its being called by the exact ID
              //->condition('re.dept', $dept)
              ->condition('re.run_duration', 25, '>')
              ->condition('re.locally_executed', 0)
              ->condition('re.run_flag', 1, '!=')
              ->condition('re.run_flag', 69, '!=')
              ->fields('re', array($field));
        $result = $query->execute()->fetchField();
        db_set_active();
        
        return $result;
    }
    
    public static function getRunExecutionsOnTpidByTimeSpan($tpid, $from, $to)
    {
         db_set_active('taf');
            $query = db_select('run_execution', 're');
            $query->condition('re.reid', $reid)
                  ->condition('run_datetime_start', array($form, $to), 'BETWEEN')
                  ->fields('re');
         db_set_active();
    }
    
    /**
     * get screenshots by reid
     * 
     * @param type $reid
     * @return type
     */
    public static function getScreenshotsByReid($reid)
    {
        db_set_active('taf');
           $query = db_select('run_execution_screenshot', 'res');
           $query->fields('res', array('resid', 'reid', 'filename', 'screenshot_img'))
                 ->condition('res.reid', $reid);
           $resultSet = $query->execute();
           db_set_active();
           
           $screenshotStore = array();
           
           foreach ($resultSet as $result)
           {
               
                $screenshotStore[] = array('resid' => $result->resid,
                                         'reid' => $result->reid,
                                         'filename' => $result->filename);
           }
           //drupal_set_message('<pre>'.  print_r($screenshotStore, 1).'</pre>');
           return $screenshotStore;
    }
    
    public static function getScreenshotByResid($resid)
    {
        db_set_active('taf');
        $query = db_select('run_execution_screenshot', 'res');
        $query->fields('res', array('filename', 'reid', 'screenshot_img'))
               ->condition('res.resid', $resid);
        $result = $query->execute()->fetchObject();
        db_set_active();
           
        $screenshotStore = array('reid' => $result->reid,
                                 'filename' => $result->filename,
                                 'image' => '<img src="data:image/jpeg;base64,'.
                                    base64_encode($result->screenshot_img).'" />',
                                 'rawImage' => $result->screenshot_img,
            );
            
        return $screenshotStore;
    }
    
    public static function getAllRerunDataByParentReid($reid)
    {
        db_set_active('taf');
            $query = db_select('rerun_relation', 'rer');
            $query->condition('rer.parent_reid', $reid)
                  ->orderBy('created_at', 'desc')
                  ->fields('rer');
            $resultSet = $query->execute();
         db_set_active();
         $rerunData = array();
         
         foreach ($resultSet as $resDt)
         {
             $rerunData[] = array(
                 'rreid' => $resDt->rreid,
                 'parent_reid' => $resDt->parent_reid,
                 'child_reid' => $resDt->child_reid,
                 'created_at' => $resDt->created_at,
                 'xmlObj' => reportsuiteXmlProcessor::processTestResultsXml($resDt->child_reid)
                 );
         }
         
         return $rerunData;
    }
    
    public static function getAllReleasePhasesByTpid($tpid)
    {
        $releasePhases = array();
        $validation = array('', 'null', '${releasephase}');
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->distinct()
                ->condition('re.tpid', $tpid)
                ->fields('re', array('release_stage'));
        $resultSet = $query->execute();
        db_set_active();
        
        foreach($resultSet as $result)
        {
            $rp = strtolower($result->release_stage);
            if(isset($rp) && !in_array($rp, $releasePhases) && !in_array($rp, $validation))
                array_push ($releasePhases, $rp);
        }
        return $releasePhases;
    }
    
    public static function getAllReidByReleasePhase($tpid, $releasePhase)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.dept', 'PST')
              ->condition('re.tpid', $tpid, '=')  
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode IN('SERVICE','SOAPUI')) OR ".
                      "(re.run_duration > 25 AND re.run_mode IN('UI', 'GRID'))")
              ->where('LOWER(re.release_stage) = :rp', array(':rp' => $releasePhase))
              ->fields('re', array('reid'));
        
        $resultSet = $query->execute();
        db_set_active();
        
        return $resultSet;
    }
    
    public static function getDateOfReleasePhase($tpid, $releasePhase, $sort)
    {
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->condition('re.dept', 'PST')
              ->condition('re.tpid', $tpid, '=')  
              ->condition('re.run_flag', 1, '!=')  
              ->condition('re.run_flag', 69, '!=')
              ->condition('re.locally_executed', 0)
              ->where("(re.run_duration > 5 AND re.run_mode = 'SERVICE') OR ".
                      "(re.run_duration > 25 AND re.run_mode IN('UI', 'GRID'))")
              ->where('LOWER(re.release_stage) = :rp', array(':rp' => $releasePhase))
              ->orderBy('re.run_datetime_start', $sort)
              ->fields('re', array('run_datetime_start'));
        
        $result = $query->execute()->fetchField();
        db_set_active();
        
        return $result;
    }
    

    public static function runExecutionFlag($reid, $flag = 0)
    {
        db_set_active('taf');
        $query = db_update('run_execution');
        $query->fields(array('run_flag' => $flag))
              ->condition('reid', $reid)
              ->execute();
        db_set_active();
        return;
    }
    
    
    public static function getSeleniumLogFile($reid)
    {
        db_set_active('taf');
        $query = db_select('run_execution_logs', 'rel');
        $query->condition('rel.reid', $reid)
              ->fields('rel', array('log_content'));
        $result = $query->execute()->fetchField();
        db_set_active();
        
        return $result;
    }
    
    public static function logActivity($logLevel, $logType, $logFunctionArea, $username, $details)
    {
        global $user;
        db_set_active('taf');
        $userHostIp = !empty($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'Not Found';
        $userHostName = !empty($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:'Not Found';
        $query = db_insert('activity_log')
                 ->fields(array(
                  'log_level' => $logLevel,
                  'log_type' => $logType,
                  'log_functionArea' => $logFunctionArea,
                  'uid_change_by' => $user->uid,
                  'username' => $username,
                  'ip_addr' => $userHostIp,
                  'hostname' => $userHostName,
                  'activity_details' => $details,
                  'created_at' => date('Y-m-d H:i:s')
                 ))
                ->execute();
        db_set_active();
    }
    
    /**
     * Latest Automation Result Store
     * @param type $tpid
     * @param type $dept
     * @param type $compileToOne
     * @return string
     */
    public static function latestCustomerAutomationResult($tpid, $dept, $compileToOne = false)
    {
//        $autoResultsStore = self::getRunExecutionResultsSummaryByReid(
//                                            self::getLastReidByTpidAndDept($tpid, $dept));
        $xmlObj = reportsuiteXmlProcessor::processTestResultsXml(
                self::getLastReidByTpidAndDept($tpid, $dept));
        
        $autoResultsStore['TAFError'] = 0;
        $autoResultsStore['TAFError'] += reportsuiteXmlProcessor::getSpecifiedOverallResult(
                                           $xmlObj, 'tafError');
        $autoResultsStore['Pass'] = reportsuiteXmlProcessor::getSpecifiedOverallResult(
                                           $xmlObj, 'passed');
        $autoResultsStore['Warning'] = reportsuiteXmlProcessor::getSpecifiedOverallResult(
                                           $xmlObj, 'warning');
        $autoResultsStore['Fail'] = reportsuiteXmlProcessor::getSpecifiedOverallResult(
                                           $xmlObj, 'failed');
        
        $autoResultsStore['Total'] = $autoResultsStore['Pass']+
                $autoResultsStore['Warning']+$autoResultsStore['Fail'];
        if($compileToOne)
        {
            $LARRepresentation = 'TC:<b>'.$autoResultsStore['Total']."</b> | ".
                                 '( <b> <span style="color:green;">'.$autoResultsStore['Pass'].'</span> / '.
                                 '<span style="color:#E68A00;">'.$autoResultsStore['Warning'].'</span> / '.
                                 '<span style="color:red;">'.$autoResultsStore['Fail'].'</span> / '.
                                 '<span>'.$autoResultsStore['TAFError'].'</span> </b>)';
            return $LARRepresentation;            
        }
        else
        {
            return $autoResultsStore;
        }
    }
    
    public static function passToFailRatio($tpid, $dept)
    {
        $resultSet = self::getAllReidByTpidAndDept($tpid, $dept);
        $PFRautoResultsStore = array('Total' => 0,
                                  'Pass' => 0,
                                  'Warning' => 0,
                                  'Fail' => 0,
                            );
        foreach($resultSet as $result)
        {
            $autoResultsStore = self::getRunExecutionResultsSummaryByReid($result->reid);
            if (!empty($autoResultsStore))
            {
                $PFRautoResultsStore['Total'] = $PFRautoResultsStore['Total'] + $autoResultsStore['Total'];
                $PFRautoResultsStore['Pass'] = $PFRautoResultsStore['Pass'] + $autoResultsStore['Pass'];
                $PFRautoResultsStore['Warning'] = $PFRautoResultsStore['Warning'] + $autoResultsStore['Warning'];
                $PFRautoResultsStore['Fail'] = $autoResultsStore['Fail'] + $autoResultsStore['Fail'];
            }
        }
        //drupal_set_message('<pre>'.print_r($PFRautoResultsStore,1).'</pre>');
        $PFRatio = 0;
        if ($PFRautoResultsStore['Total'] != 0 )
        {
            $PFRatio = number_format(
                    ($PFRautoResultsStore['Pass']+$PFRautoResultsStore['Warning'])
                    /$PFRautoResultsStore['Fail'],
                    2);
        }
        
        return $PFRatio;
    }
    
      /**
     * Method to process automationResult by Run Result Data from run_result Table
     * 
     * @param type $reid - if Reid is different to one in result set
     * @param type $resultSet
     * @return type
     */
    public static function processAutomationResultOnRunResultsData($reid, $resultSet)
    {
           $automationResultStore = array();
           foreach ($resultSet as $result)
           {  
            $automationResultStore[$result->rrid]['reid'] = $result->reid;
            $automationResultStore[$result->rrid]['ScenarioName'] = $result->scenario_identifier;
            $automationResultStore[$result->rrid]['Total'] = $result->total_test_cases;
            
            //if $reid is different
            if($reid != null)
            {
                //drupal_set_message($reid);
                $resArr = reportsuiteXmlProcessor::getOverallResultsFromScenariosInSuite(
                          reportsuiteXmlProcessor::processTestResultsXml($reid), $result->scenario_identifier);
                $automationResultStore[$result->rrid]['Pass'] = $resArr['Passed'];
                $automationResultStore[$result->rrid]['Warning'] = $resArr['Warning'];        
                $automationResultStore[$result->rrid]['Fail'] = $resArr['Failed'];
                $automationResultStore[$result->rrid]['TAFError'] = $resArr['TAFError'];
            }
            else
            {
                 $resArr = reportsuiteXmlProcessor::getOverallResultsFromScenariosInSuite(
                          reportsuiteXmlProcessor::processTestResultsXml($result->reid), $result->scenario_identifier);
                 $automationResultStore[$result->rrid]['Pass'] = $resArr['Passed'];
                $automationResultStore[$result->rrid]['Warning'] = $resArr['Warning'];        
                $automationResultStore[$result->rrid]['Fail'] = $resArr['Failed'];
                $automationResultStore[$result->rrid]['TAFError'] = $resArr['TAFError'];
            }
            
            $automationResultStore[$result->rrid]['Flag'] = $result->result_flag;
           }
           //drupal_set_message('<pre>'.print_r($automationResultStore, 1).'</pre>');
           return $automationResultStore;
    }
    
    /**
     * Method to get All result <br /> Can be Interpret as a InterfaceMethod to GetAllReidByTpidAndDept()
     * <br /> But with Additional Process
     * 
     * @param type $tpid
     * @param type $releaseStage
     * @param type $combineTotal
     * @param type $summary
     * @return type
     */
    public static function getAllResultsByTpidAndReleaseStage($tpid, $releaseStage, $combineTotal = false, $summary = false)
    {
        
        if($summary){
            $reResultSet = $releaseStage == 'default'?self::getAllReidByTpid($tpid, 20):
                self::getAllReidByTpidAndReleaseStage($tpid, $releaseStage, 20);
        }else{
            $reResultSet = $releaseStage == 'default'?self::getAllReidByTpid($tpid):
                self::getAllReidByTpidAndReleaseStage($tpid, $releaseStage);
        }
        
        $automationResultStore = self::processAutomationResultByReidSet($reResultSet, TRUE, $combineTotal);
        if($summary) ksort ($automationResultStore);
        return $automationResultStore;
    }
    
    public static function getAllResultsByReidCollection($reidCollection)
    {
        return self::processAutomationResultByReidSet($reidCollection, true, true);
    }

    private static function processAutomationResultByReidSet($reidCollection, $isObject, $combineTotal)
    {
        $i = 0;
        $automationResultStore = array();
        
        foreach ($reidCollection as $reResults)
        {
            $reid = $isObject?$reResults->reid:$reResults;
            $automationResultStore[$reid] = self::getAllRunResultsInfoByReid($reid);
        }
        
        if($combineTotal)
        {
            //drupal_set_message('<pre>'.  print_r($automationResultStore, 1).'</pre>');
            $automationResultStoreAdjustment = array();
            foreach($automationResultStore as $reid => $runResultDetail)
            {
                $automationResultStoreAdjustment[$reid] = array('Scenarios' => array(),
                                                                'Total' => 0, 
                                                                'Pass' => 0, 
                                                                'Warning' => 0, 
                                                                'Fail' => 0,
                                                                'TAFError' => 0);
                //drupal_set_message('<pre>'.print_r($runResultInfo, 1).'</pre>');
                foreach($runResultDetail as $rrid => $value)
                {
                    if(!in_array($value['ScenarioName'], 
                            $automationResultStoreAdjustment[$value['reid']]['Scenarios']))
                    {
                        array_push($automationResultStoreAdjustment[$value['reid']]['Scenarios'], 
                                   $value['ScenarioName']);
                    }
                    
                    $automationResultStoreAdjustment[$value['reid']]['Total'] += $value['Total'];
                    $automationResultStoreAdjustment[$value['reid']]['Pass'] += $value['Pass'];
                    $automationResultStoreAdjustment[$value['reid']]['Warning'] += $value['Warning'];        
                    $automationResultStoreAdjustment[$value['reid']]['Fail'] += $value['Fail'];
                    $automationResultStoreAdjustment[$value['reid']]['TAFError'] += $value['TAFError'];
                }
            }
            unset($automationResultStore);
            return $automationResultStoreAdjustment;
        }
        else
        {
            return $automationResultStore;
        }
    }

    /**
     * Method to reverse the array cachce from Key being Run Results into Run Execution Grouping
     * 
     * <br />
     * This Method also provide additional details such as; <br />
     * combine Pass and Warning <br />
     * Pass Rate <br />
     * <br />
     * It will also automatically combine the total of multiple scenario runs
     * 
     * @param type $reid
     * @return type
     */
    public static function adjustRunResultStoreToRunExecutionStore($automationResultStore)
    {
            $automationResultStoreAdjustment = array();
            $totalCombination = array('Total' => 0, 'Total Passed' => 0, 'Passed' => 0, 'Warning' => 0, 
                                                'Failed' => 0, 'TAFError' => 0);
            foreach($automationResultStore as $rrid => $value)
            {
                $reid = $value['reid'];
                $scenarioName = $value['ScenarioName'];
                $total = $value['Total'];
                $pass = $value['Pass'];
                $warning = $value['Warning'];
                $fail = $value['Fail'];
                $tafErr = $value['TAFError'];
                $flag = $value['Flag'];
                
                $tempStore = array('rrid' => $rrid,
                                   'Total' => $total,
                                   'Total Passed' => $pass+$warning,
                                   'Pass Rate' => self::calculatePassRate($total, 
                                                    $pass+$warning),
                                   'Pass' => $pass,
                                   'Warning' => $warning,
                                   'Fail' => $fail,
                                   'TAFError' => $tafErr,
                                   );
                
                $automationResultStoreAdjustment[$reid][$scenarioName] = $tempStore;
                if(!isset($automationResultStoreAdjustment[$reid]['CombineTotal'])) 
                    $automationResultStoreAdjustment[$reid]['CombineTotal'] = $totalCombination;
                $automationResultStoreAdjustment[$reid]['CombineTotal']['Total'] += $total;
                $automationResultStoreAdjustment[$reid]['CombineTotal']['Total Passed'] += ($pass+$warning);
                $automationResultStoreAdjustment[$reid]['CombineTotal']['Passed'] += $pass;
                $automationResultStoreAdjustment[$reid]['CombineTotal']['Warning'] += $warning;
                $automationResultStoreAdjustment[$reid]['CombineTotal']['Failed'] += $fail;
                $automationResultStoreAdjustment[$reid]['CombineTotal']['TAFError'] += $tafErr;
            }
            
            return $automationResultStoreAdjustment;
    }
    
    /**
     * Method to calculate pass rate and additional to create visual effect
     * 
     * @param type $total
     * @param type $passAndWarning
     * @param type $addVisiualFx
     * @return string
     */
    public static function calculatePassRate($total, $passAndWarning, $addVisiualFx = false)
    {
        if($total == 0) return 'NA';
        $passRate = ($passAndWarning/$total)*100;
        $passRate = number_format($passRate, 2).'%';
        
        if($addVisiualFx)
        {
            $passRate = reportsuite::addVisiualEffectOnPR($passRate);
        }
        
        return $passRate;
    }
    
    public static function getDistinctEnvironmentByTpId($tpid)
    {
        $envIp = array();
        $validation = array('', 'null', '${releasephase}', 'HTTP://', 
            'HTTPS://','http://','https://',);
        db_set_active('taf');
        $query = db_select('run_execution', 're');
        $query->distinct()
                ->condition('re.tpid', $tpid)
                ->fields('re', array('test_environment'));
        $resultSet = $query->execute();
        db_set_active();
        
        foreach($resultSet as $result)
        {
            $rp = strtolower($result->test_environment);
            if(isset($rp) && !in_array($rp, $envIp) && !in_array($rp, $validation))
                array_push ($envIp, $rp);
        }
        return $envIp;
    }
    
    public static function getReidByFilterQuery(){
        
    }
    
}

?>
