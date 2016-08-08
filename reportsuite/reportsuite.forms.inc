<?php

function reportsuite_resultFailureReason_form($form, &$form_state)
{
    $reid = $form_state['build_info']['args'][0];
    
    $form = reportsuite_failureReason_form_template($reid);
    
    $form['reid'] = array(
        '#type' => 'hidden',
        '#value' => $reid
        );
    
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
    );
    
    return $form;
}

function reportsuite_resultFailureReason_form_submit($form, &$form_state)
{
    reportsuiteXmlProcessor::setFailureReason($form_state['values']['reid'], 
            array('failureReason' => $form_state['values']['failureReason'],
                  'failureReasonComment' => $form_state['values']['failureReasonComment']));
    
    drupal_set_message('Submitted');
}

function reportsuite_testcaseFailureReason_form($form, &$form_state)
{
    $reid = $form_state['build_info']['args'][0];
    $scenario = $form_state['build_info']['args'][1];
    $testCaseId = $form_state['build_info']['args'][2];
    
    $form = reportsuite_failureReason_form_template($reid, 
            array('scenario' => $scenario,
                  'testCaseId' => $testCaseId));
    
    $form['reid'] = array(
        '#type' => 'hidden',
        '#value' => $reid
        );
    
    $form['scenario'] = array(
        '#type' => 'hidden',
        '#value' => $scenario
        );
    
    $form['testCaseId'] = array(
        '#type' => 'hidden',
        '#value' => $testCaseId
        );
    
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
    );
    
    return $form;
    
}

function reportsuite_testcaseFailureReason_form_submit($form, &$form_state)
{
    reportsuiteXmlProcessor::setFailureReason($form_state['values']['reid'], 
            array('scenario' => $form_state['input']['scenario'],
                  'testCaseId' => $form_state['input']['testCaseId'],
                  'failureReason' => $form_state['values']['failureReason'],
                  'failureReasonComment' => $form_state['values']['failureReasonComment']));
    
    drupal_set_message('Submitted');
}

function reportsuite_failureReason_form_template($reid, $additData = array())
{
    $failureReason = reportsuiteresult::getCommonFailureReason();
    
    $form['failureReason'] = array(
      '#title' => t('Failure Reason'),
      '#type' => 'select',
      '#required' => true,
      '#options' => $failureReason['FAIL'],
      '#description' => t('Select a common failure reason point. 
            If point is unavailable then select other and abbreviate failure before
            jotting down your observation')
    );
    
    $form['failureReasonComment'] = array(
      '#title' => t('Observation Comment'),
      '#type' => 'textarea',
      '#required' => true,
    );
    
    $existingFr = reportsuiteXmlProcessor::getFailureReason($reid, $additData);
    
    if($existingFr !== FALSE)
    {
        $form['failureReason']['#default_value'] = $existingFr['failureReason'];
        $form['failureReasonComment']['#default_value'] = 
            $existingFr['failureReasonComment'];
    }
    
    return $form;
}

function reportsuite_runExecutionFlag_form($form, &$form_state) 
{
        $reid = $form_state['build_info']['args'][0];
        $addImage = theme('image', array(
                    'path' => drupal_get_path('module', 'reportsuite').
                             '/lib/thumbs_down_gladiator.gif',
                    'alt' => 'FAIL',
                    'title' => 'Epic Fail',
                    'width' => '50%',
                    'height' => '50%',
                    'attributes' => array('id' => 'projectSuiteRunImg'),
                    ));
        
        
        $content = t('<b>Are you sure you want to flag Run <u>'.$reid.'</u> as a bad run?</b>').
                    '<br />'.$addImage;
        ;
        
        $form['_reid'] = array(
            '#type' => 'hidden',
            '#value' => $reid,
        );
        
        $form['content'] = array(
            '#markup' => $content.'<br />',
        );
        
        $form['warning'] = array(
            '#markup' => t('This action will <b>remove run result</b>
                            from reporting suite and <b>cannot</b> be undone.').'<br />',
        );
        
        $form['submitBR'] = array(
            '#type' => 'submit',
            '#value' => 'Bad Run'
        );
        
        $form['submitCancel'] = array(
            '#type' => 'submit',
            '#value' => 'Cancel'
        );
        
        return $form;
}

function reportsuite_runExecutionFlag_form_submit($form, &$form_state) 
{
    if($form_state['values']['op'] === 'Bad Run') 
    {
        $reid = $form_state['values']['_reid'];

        reportsuitequeries::runExecutionFlag($reid, 1);			
        drupal_set_message(t('Run - '.$reid.' has been marked as Bad Run'));
    }
}


function reportsuite_reRun_form($form, &$form_state)
{
    $reid = $form_state['build_info']['args'][0];
    //rrd - runResultDetail
    $rrd = reportsuitequeries::getRunExecutionDetailsByReid($reid);
    
    $recursiveCases = false;
    
    
    $form['reid'] = array('#type' => 'hidden', '#value' => $reid);
    
    $runType = reportsuite::getRunTypeEnum(69);
   $configurationInfo = '
  <fieldset class="collapsible collapsed"> 
    <legend><span class="fieldset-legend">Configuration Information</span></legend> 
      <div class="fieldset-wrapper">'.
           '<b>Run Type:</b> '.$runType['name'].' on <b>Run-'.$reid.'</b><br />'.
           '<b>Environment:</b> '.$rrd->test_environment.'<br />'.
           '<b>Browser:</b> '.$rrd->browser_assign.'<br />'.
           '<b>Run Mode:</b> '.$rrd->run_mode.'<br />'.
           '<b>Release Phase:</b> '.$rrd->release_stage.'<br />'.
           '<b>TestCycle:</b> '.$rrd->test_cycle.'
    </div>
 </fieldset>';
    
    $form['failedCasesLabel'] = array(
       '#markup' =>  '<h2><b>Failed Test Cases</b></h2>
                <p>Please select which of these failed cases you would like to run again</p>
                <p><b>Note:</b> by default TailMatrixTDP will be switched OFF. You will need to do a fresh run
                in order todo so.</p>'
    );
     $form['configuration'] = array(
        '#markup' => $configurationInfo,
    );
    $testResults = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData(
        reportsuiteXmlProcessor::processTestResultsXml($reid), 'scenario');
    $failedTestCases = reportsuiteXmlProcessor::getFailedTestCases($testResults);
    $scenarioList = array();
   foreach ($failedTestCases as $scenario => $failedCases)
   {
       $scenarioList[] = $scenario;
       $failedCaseOption = array();
       //do not give the option to re run cases with Data but if a single or multiple case fail
       //within the test case boundry of looping data then give the option to run the whole test case 
       //on all data
       foreach ($failedCases as $tcid => $caseRes)
       {
           $addit = '';
           //Will remove any test cases with duplicates
//           if(strpos($tcid, 'Data'))
//           {
//                $tcid = substr($tcid, 0, strpos($tcid, 'Data'));
//                $recursiveCases = true;
//                $addit = '*';
//           }
           if(!empty($tcid))
           $failedCaseOption[$tcid] = $tcid.$addit ; 
           
       }
       
       if($scenario != 'Unassigned')
       {
            $form[$scenario] = array('#prefix' => '<div class="rrInfoBlock rribExt">', '#suffix' => '</div>');
            $form[$scenario][$scenario.'_label']  = array('#markup' => '<h2><u>'.$scenario.'</u></h2>');
            $form[$scenario][$scenario.'_cases'] = array(
                 '#type' => 'checkboxes',
                 '#options' => $failedCaseOption,
                 '#description' => 'Failed Test Cases',
                 '#default_value' => array_keys($failedCaseOption),
             );
       }
   }
   
   if($recursiveCases)
   {
       $form['recursiveNotice'] = array(
           '#markup' => '*Cases that are recursive iterating through multiple data rows',
       );
   }
    
   $form['scenarioList'] = array('#type' => 'hidden', '#value' => implode('::', $scenarioList));
   
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Re-run Automation'
    );
    
    return $form;
}

function reportsuite_reRun_form_submit($form, &$form_state)
{
    include_once drupal_get_path('module', 'projectcontrol').'/projectcontrol.scriptHandler.class.php';
    include_once drupal_get_path('module', 'projectcontrol').'/projectcontrol.class.php';
    global $user;
 //  drupal_set_message('<pre>' . print_r($form_state['input'], 1) . '</pre>');
//    drupal_set_message('<pre>' . print_r($form_state['values'], 1) . '</pre>');
    $formValues = $form_state['input'];
    $scenarioList = explode('::', $formValues['scenarioList']);
    $selectedCases = array();
    
    //This Algorithm is different to the projectcontrol.forms.inc:configurationRunForm
    $projectInfo = projectcontrol::getProjectBasicDetails(
                                reportsuitequeries::getTpidByReid($formValues['reid']));
    $customer = str_replace(" ", "", $projectInfo['customerAlias']);
    foreach ($scenarioList as $scenario)
    {
        if($scenario != 'Unassigned')
        {
            $scenarioCasedPicks = str_replace('.xls', '_xls', $scenario);
            $scenarioXlsless = str_replace('.xls', '', $scenario);
            $scenarioXlsless = str_replace($customer.'\\', '', $scenarioXlsless);
            $scenarioXlsless = str_replace($customer.'/', '', $scenarioXlsless);
            $selectedCasesCount = 0;
            $numberOfTotalCases = 0;
              foreach ($formValues[$scenario.'_cases'] as $case => $selected)
              {
                $numberOfTotalCases++;
                 if(isset($formValues[$scenarioCasedPicks.'_cases'][$case]))
                 {
                     $selectedCasesCount++;
                     $selectedCases[$scenarioXlsless]['selectedCases'][] = $case;
                 }
              }
              $selectedCases[$scenarioXlsless]['selectedCasesCount'] = $selectedCasesCount;
              // Not Applicable - 
              $selectedCases[$scenarioXlsless]['numberOfTotalCases'] = 999999999;
        }
    }
    
    
    $rrd = reportsuitequeries::getRunExecutionDetailsByReid($formValues['reid']);
    
    $testEnv = split('://', $rrd->test_environment);
    
    //Will have to hard code build parameters
    $buildWithParameters = array(
            'OverrideDefaultSettings' => true,
            'protocol' => $testEnv[0],
            'environmentAddress' => $testEnv[1],
            'browserType' => $rrd->browser_assign,
            'runMode' => $rrd->run_mode,
            'testCycle' => $rrd->test_cycle,
            'releasePhase' => $rrd->release_stage,
            'runByUser' => $user->mail,
            'runType' => 69,
            'reRunId' => $rrd->reid,
            'tailMatrixTDP' => 'OFF',
        );
    
    
    projectcontrolscripthandler::setScenarioCasesToTestSuiteJson(
                $customer, $selectedCases, true);
    projectcontrolscripthandler::convertTestSuiteJsonToXls(
                $customer, true);
    //projectcontrol::commitTestSuiteChanges(null, $customer, true);
    $url = $projectInfo['jenkinsJobPath'].'buildWithParameters?';
    
    projectcontrol::executeJenkinsRun($url, $buildWithParameters);
    drupal_set_message("Re-running failures. Please goto Jenkins to see progress. 
        Results will be publish on this Result Detail page Run-".$rrd->reid);
}


function reportsuite_customerSummaryTable_form($form, &$form_state)
{
    $header = $form_state['build_info']['args'][0];
    $row = $form_state['build_info']['args'][1];
    
    if(isset($form_state['storage']['badRunClarity']))
    {
         $addImage = theme('image', array(
                    'path' => drupal_get_path('module', 'reportsuite').
                             '/lib/thumbs_down_gladiator.gif',
                    'alt' => 'FAIL',
                    'title' => 'Epic Fail',
                    'width' => '50%',
                    'height' => '50%',
                    'attributes' => array('id' => 'projectSuiteRunImg'),
                    ));
        
        
        $content = '<h1><b>Are you sure?</b></h1><br />'.$addImage;
        $content .= '<div style="border:2.5px dotted red">';
        $content .= "<br /><center><b>You are about to mark the following as bad Runs;</b></center>";
        
        foreach($form_state['storage']['reidSelection'] as $reid => $rSel)
        {
                if(!empty($rSel)) $rowClarity[] = $row[$rSel];
        }
        $content .= theme('table', array ('header' => $header, 'rows' => $rowClarity));
        
        $content .= '</div>';
        
        $form['content'] = array(
            '#markup' => $content.'<br />',
        );
        
        
        $form['warning'] = array(
            '#markup' => t('This action will <b>remove run result</b>
                            from reporting suite and <b>cannot</b> be undone.').'<br />',
        );
        
        $form['submitBR'] = array(
            '#type' => 'submit',
            '#value' => 'Bad Run'
        );
        
        $form['submitCancel'] = array(
            '#type' => 'submit',
            '#value' => 'Cancel'
        );
    }
    else
    {
        
        $form['badRun'] = array('#type' => 'submit', '#value' => t('Mark as BadRun'));

        $form['customerSummary'] = array(
          '#type' => 'tableselect',
          '#header' => $header,
          '#options' => $row,
          '#empty' => t('No content available.'),
        );
    }
    
    return $form;
}


function reportsuite_customerSummaryTable_form_submit($form, &$form_state)
{
    if($form_state['clicked_button']['#id'] == "edit-badrun")
    {
        $form_state['storage']['badRunClarity'] = TRUE;
        
        $form_state['storage']['reidSelection'] = $form_state['values']['customerSummary'];
        $form_state['rebuild'] = TRUE;
    }
    else
    {
        if($form_state['clicked_button']['#id'] == "edit-submitbr")
        {
            $marked = '';
            foreach($form_state['storage']['reidSelection'] as $reid => $rSel)
            {
                    if(!empty($rSel)) 
                    {
                        $marked .= $rSel.',';
                        reportsuitequeries::runExecutionFlag($rSel, 1);	
                    }
            }
            drupal_set_message($marked. ' has been filtered out and MARKED as bad Runs');
        }
        else
        {
            drupal_set_message("Nothing was processed");
        }
        unset($form_state['storage']);
    }
    
    
}

function reportsuite_overrideOutcomeForm($form, &$form_state, $reid, $testCaseId)
{
    //showMaintaiananceMsg();
    $tpid = reportsuitequeries::getTpidByReid($reid);
    $customerAlias = reportsuite::customerAlias($tpid);
    
    $testCaseOutcomeDetails = reportsuite::getTestCaseOutcomeData($reid, $testCaseId);
    
    $parsedDur = reportsuite::parseHHMMSSDuration($testCaseOutcomeDetails['duration']);
    
    $cssStylePatch = reportsuite::getPassFailWarningCSSStyle($testCaseOutcomeDetails['outcome']);
    
    $testCaseInfoDisplay = "<u><h4><b>Current</b> Result Outcome</h4></u>";
    
    $testCaseInfoDisplay .= "<b>Status:</b> <span style=\"padding:2.5px;".$cssStylePatch."\">".
                            $testCaseOutcomeDetails['outcome']."</span><br />";
    $testCaseInfoDisplay .= "<b>Duration:</b> ".$parsedDur."<br />";
    $testCaseInfoDisplay .= "<b>description:</b> ".$testCaseOutcomeDetails['description']."<br />";
    
    if($testCaseOutcomeDetails['outcome'] != 'Passed')
    {
        $testCaseInfoDisplay .= '<u><b>Error Message</b></u>: <br />
                <table border=1 style="'.$cssStylePatch.'font-size:12px;"><tr><td>'.
                str_replace("::", "<br /><br />", $testCaseOutcomeDetails['failDets']).
                '</td></tr></table>';
    }
    
    if(!empty($testCaseOutcomeDetails['originalOutcome']))
    {
        $cssStylePatchOrg = reportsuite::getPassFailWarningCSSStyle(
                $testCaseOutcomeDetails['originalOutcome']['status']);
        $testCaseInfoDisplay .= '<u><h4><b>Original</b> Result Outcome</h4></u>';
        $testCaseInfoDisplay .= "<b>Status:</b> <span style=\"padding:2.5px;".$cssStylePatchOrg."\">".
                $testCaseOutcomeDetails['originalOutcome']['status']."</span><br />";
        $testCaseInfoDisplay .= "<b>Details:</b> ".
                $testCaseOutcomeDetails['originalOutcome']['details']."<br />";
    }
    //drupal_set_message('<pre>'.print_r($testCaseOutcomeDetails, 1).'</pre>');
    
    drupal_set_title($customerAlias['name'].' Automation | Result Details Form | Run-'.$reid);
    
    $form['subHeading'] = array(
                          '#type' => 'item',
                          '#title' => t("<h2>Override Test Case Outcome</h2>"),
                          '#description' => ""
                          );
    
    $form['testcaseDetail'] = array(
                              '#type' => 'item',
                              '#title' => t("<h3>Test Case [".$testCaseId."] Information</h3>"),
                              '#markup' => $testCaseInfoDisplay
                              );
    
    $form['testCaseInfo']  = array(
                            '#type' => 'hidden',
                            '#value' => array('reid' => $reid,
                                              'testCaseId' => $testCaseId,
                                              'details' => $testCaseOutcomeDetails,
                                        ),
                            );   
                         
    $form['tcChangedOutcome'] = array(
                         '#type' => 'select',
                         '#title' => t('Outcome'),
                         '#options' => array('Passed' => 'Passed',
                                            'Warning' => 'Warning',
                                            'Failed' => 'Failed',
                                            'TAFError' => 'TAFError'),
                         '#default_value' => $testCaseOutcomeDetails['outcome'],
                         '#required' => true,
                         '#description' => t('NOTE: If changed you must indicate a reason below')
                        );
    
    
    $form['reason'] = array(
                       '#type' => 'textarea',
                       '#title' => t('Reason for outcome change'),
                       '#reziable' => TRUE,
                        '#required' => true,
                        '#default_value' => $testCaseOutcomeDetails['failDets'],
                       );

    $form['username'] = array(
                       '#type' => 'textfield',
                       '#title' => t('Your Name'),
                       '#description' => t('Name of Yourself to identify who has 
                           override the outcome of this test case.'),
                        '#required' => true,
                       );
    
    $form['cancel'] = array(
                       '#type' => 'submit',
                       '#value' => t('Cancel'),
                       );
    
    $form['submit'] = array(
                       '#type' => 'submit',
                       '#value' => t('Change'),
                       );
    
    return $form;
}

function reportsuite_overrideOutcomeForm_validate($form, &$form_state)
{
    //drupal_set_message('<pre>'.print_r(&$form_state,1).'</pre>');
    if ($form_state['values']['op'] == 'Change')
    {
        if ($form_state['values']['testCaseInfo']['details']['outcome'] != 
            $form_state['values']['tcChangedOutcome'])
        {
            if (empty($form_state['values']['reason']))
            {
                form_set_error('reason', t('Text Area <b>[Reason for outcome change]</b> cannot be empty if you would 
                    would like to override test case outcome'));
            }
        }
    }
}

function reportsuite_overrideOutcomeForm_submit($form, &$form_state)
{
    
    if ($form_state['values']['op'] == 'Change')
    {
            $testCaseId = $form_state['values']['testCaseInfo']['testCaseId'];
    
        if ($form_state['values']['testCaseInfo']['details']['outcome'] == 
            $form_state['values']['tcChangedOutcome'])
        {
            drupal_set_message(t('No changes have been made to the Test Case 
               ['.$testCaseId.']'));
        }
        else
        {
            
            
            $dataArr = array('changedOutcomeValue' => $form_state['values']['tcChangedOutcome'],
                             'reason' => $form_state['values']['reason']. '<br /> 
                              Outcome Override by: '.$form_state['values']['username']. ' ('.date('Y-m-d H:i:s').')',
                             'testCaseInfo' => $form_state['values']['testCaseInfo'],
                            );
            
            $logDataCompiledInfo = 'Test Case [tc-'.$testCaseId.'] outcome on run execution
                                   [reid-'.$form_state['values']['testCaseInfo']['reid'].']
                                   has been overwritten. Details as Follows [changedOutcome-'.
                                   $form_state['values']['tcChangedOutcome'].'|reason-'.
                                   $form_state['values']['reason'].']';
            
            reportsuiteXmlProcessor::editTestCaseOutcome($dataArr);
            reportsuitequeries::logActivity('informational', 'UserAction', 'overrideTcOutcome', 
                                            $form_state['values']['username'], $logDataCompiledInfo);
            //drupal_set_message('<pre>'.print_r(&$form_state,1).'</pre>');
            
            $changeMsg = 'Test Case <u><b>'.$testCaseId.'</b></u> outcome changed.<br /><br />
                          Outcome changed from <b>'.$dataArr['testCaseInfo']['details']['outcome'].
                          '</b> to <b>'.$form_state['values']['tcChangedOutcome'].'</b>';
            
            drupal_set_message($changeMsg);
            
        }
        
    }
    
}

function reportsuite_customerSummaryFilter_form($form, &$form_state, $tpid){
    
  $step = $form_state['clicked_button']['#value']=="Next"?2:1;
  $submitButton = 'Next';
  $dateFrom = date('Y-m-d H:i:s');
  $dateTo = date('Y-m-d H:i:s');
  $format = 'Y-m-d H:i';
  
  $form['resultFilter'] = array(
    '#type' => 'fieldset', 
    '#title' => t('Filter Results'), 
    '#collapsible' => TRUE, 
    '#collapsed' => FALSE,
  );
  
  switch($step)
  {
      case 1:
        $form['resultFilter']['filterType'] = array(
            '#type' => 'radios',
            '#title' => t('Filter By'),
            '#options' => reportsuite_filterType_options(),
        );
        break;
      case 2:
        $submitButton = 'Filter Results!';
        $form['resultFilter']['heading'] = array(
            '#type' => 'item',
            '#title' => t(''),
            '#markup' => '<h2>'.$form_state['values']['filterType'].' Filter</h2>',
        );
        if($form_state['values']['filterType'] == 'Date')
        {
            $form['resultFilter']['from'] = array(
                '#type' => 'date_popup', // types 'date_text' and 'date_timezone' are also supported. See .inc file.
                '#title' => t('From'),
                '#default_value' => $dateFrom, 
                '#date_format' => $format,
                '#date_label_position' => 'within', // See other available attributes and what they do in date_api_elements.inc
                '#date_timezone' => 'America/Chicago', // Optional, if your date has a timezone other than the site timezone.
                '#date_increment' => 15, // Optional, used by the date_select and date_popup elements to increment minutes and seconds.
                '#date_year_range' => '-3:+3', // Optional, used to set the year range (back 3 years and forward 3 years is the default).
             );
             $form['resultFilter']['to'] = array(
                '#type' => 'date_popup', // types 'date_text' and 'date_timezone' are also supported. See .inc file.
                '#title' => t('To'),
                '#default_value' => $dateTo, 
                '#date_format' => $format,
                '#date_label_position' => 'within', // See other available attributes and what they do in date_api_elements.inc
                '#date_timezone' => 'America/Chicago', // Optional, if your date has a timezone other than the site timezone.
                '#date_increment' => 15, // Optional, used by the date_select and date_popup elements to increment minutes and seconds.
                '#date_year_range' => '-3:+3', // Optional, used to set the year range (back 3 years and forward 3 years is the default).
             );
        }else{
            
           $form['resultFilter']['filter'] = array(
                '#type' => 'select',
                '#title' => $form_state['values']['filterType'].' '.t('Filter'),
                '#options' => drupal_map_assoc(reportsuite_filter_dropdown_options(
                        $form_state['values']['filterType'], $tpid)),
            ); 
        }
        
        $form['resultFilter']['runType'] = array(
            '#type' => 'select',
            '#title' => t('Run Type'),
            '#options' => array(
               9999 => 'All Runs',
               0 => 'Official Run',
               2 => 'Test Run',
               3 => 'Maintenance',
               4 => 'Sanity',
               70 => 'Salt Integration',
            ),
        );
        $form['filterBy'] = array(
            '#type' => 'hidden', 
            '#value' => $form_state['values']['filterType']
                );
        break;
  }

  
  if(isset($_SESSION['filterSubmission'])){
      $form['resultFilter']['reset'] = array(
        '#type' => 'submit',
        '#value' => t('Reset'),
      );
  }
  
  $form['resultFilter']['submit'] = array(
    '#type' => 'submit',
    '#value' => t($submitButton),
  );
      
     return $form;
}

function reportsuite_customerSummaryFilter_form_submit($form, &$form_state)
{
    //drupal_set_message('<pre>' . print_r($form_state, 1) . '</pre>');
    if ($form_state['clicked_button']['#value'] == 'Next') {
        $form_state["rebuild"] = TRUE;
    }else if ($form_state['clicked_button']['#value'] == 'Reset') {
        unset($_SESSION['filterSubmission']);
    }else{
        $_SESSION['filterSubmission'] = $form_state['values'];
    }
}

function reportsuite_filterType_options() {
  // drupal_map_assoc() just makes an array('String' => 'String'...).
  return drupal_map_assoc(
    array(
      t('Release Phase'),
      t('Environment'),
      t('Date'),
    )
  );
}

function reportsuite_filter_dropdown_options($key = '', $tpid) {
    
    $optionResults = array();
    switch($key){
        case 'Release Phase':
            $optionResults = reportsuitequeries::getAllReleasePhasesByTpid($tpid);
            break;
        
        case 'Environment':
            $optionResults = reportsuitequeries::getDistinctEnvironmentByTpId($tpid);
            break;
    }
    return $optionResults;
}


function reportsuite_exportResults_form($form, &$form_state, $reid){
    
  $step = $form_state['clicked_button']['#value']=="Next"?$form_state['storage']['steps']++:1;
  $form_state['storage']['steps'] = $step;
  drupal_set_title('Export Results Step '.$step);
  
  //drupal_set_message("REID=".$reid);
  //drupal_set_message('<pre>' . print_r($form_state, 1) . '</pre>');
  
  $_SESSION['exportResults']['reid'] = $reid;
  
  $form['subLinks'] = array(
            '#type' => 'item',
            '#title' => t(''),
            '#markup' => reportsuite::sublinksRunResultDetails('Export Results', $reid),
        );
  
  $form['exportResults'] = array(
    '#type' => 'fieldset', 
    '#title' => t('Export Results'), 
    '#collapsible' => TRUE, 
    '#collapsed' => FALSE,
  );
  
  switch($step)
  {
      ////**************////
      //STEP 1
      ////**************////
      case 1:
          
        $form['exportResults']['Heading'] = array(
            '#type' => 'item',
            '#title' => t(''),
            '#markup' => '<h1>Full Path to test cases</h1>',
        );
        $form['exportResults']['pathToTestCases'] = array(
            '#type' => 'textfield',
            '#title' => t('Please input the full path of where test case is located in Ap-Test '
                    . '(just the path with / at the end)'),
            '#required' => true,
        );
          
        $form['exportResults']['exampleTestCasePath'] = array(
            '#type' => 'item',
            '#title' => t(''),
            '#markup' => '<u><strong>TEST CASE EXAMPLE BELOW</strong></u><br />'.
            theme('image', array(
                'path' => drupal_get_path('module', 'reportsuite').
                          '/lib/testCasePathInputExample.png',
                'alt' => 'FAIL',
                'title' => 'Epic Fail',
                'width' => '60%',
                'height' => '60%',
                'attributes' => array('id' => 'testcase input'),
                )).'<br />'.
            theme('image', array(
                'path' => drupal_get_path('module', 'reportsuite').
                          '/lib/testCasePathExample.png',
                'alt' => 'FAIL',
                'title' => 'Epic Fail',
                'width' => '100%',
                'height' => '100%',
                'attributes' => array('id' => 'testcase input'),
                )),
        );
        
         $form['exportResults']['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Next'),
          );
        break;
    
      ////**************////
      //STEP 2
      ////**************////
      case 2:
          $form['exportResults']['Heading'] = array(
                '#type' => 'item',
                '#title' => t(''),
                '#markup' => '<h1>Test Cases Synchronization</h1>',
            );
        $form['exportResults']['testCaseSynced'] = array(
            '#type' => 'radios',
            '#title' => t('Is the test case ID\'s and name the same?'),
            '#options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            ),
            '#required' => true,
        );
          
        $form['exportResults']['exampleTestCase'] = array(
            '#type' => 'item',
            '#title' => t(''),
            '#markup' => 'Example Test Case ID <br />'.
            theme('image', array(
                'path' => drupal_get_path('module', 'reportsuite').
                          '/lib/sameTestCaseExample.png',
                'alt' => 'FAIL',
                'title' => 'Epic Fail',
                'width' => '100%',
                'height' => '100%',
                'attributes' => array('id' => 'testcase input'),
                )),
        );
         $form['exportResults']['back'] = array(
            '#type' => 'submit',
            '#value' => t('Back'),
          );
         $form['exportResults']['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Next'),
          );
            
        break;
    
      ////**************////
      //STEP 3
      ////**************////
      case 3:
          
          $submitButtonExcel = 'Export to Excel';
            $submitButtonCSV = 'Export to CSV';
            $testResData = reportsuiteXmlProcessor::processXmlIntoTestCasesResultData(
                    reportsuiteXmlProcessor::processTestResultsXml($reid, true));
            if($form_state['values']['testCaseSynced'] == 'yes')
            {
               $form['exportResults']['Heading'] = array(
                    '#type' => 'item',
                    '#title' => t(''),
                    '#markup' => '<h1>Review Test Cases before Export</h1> <br />'
                   . 'Change the Test Cases Names below to match with those you have in aptest',
                );
               // drupal_set_message('<pre>' . print_r($testResData, 1) . '</pre>');
                $table = '<table>';
                $table .= '<tr>'
                        . '<th>Test Case ID</th>'
                        . '<th>Duration</th>'
                        . '<th>Status</th>'
                        . '</tr>';

                foreach($testResData as $testCaseID => $data){
                  //  drupal_set_message('<pre>' . print_r($data, 1) . '</pre>');
                    if($testCaseID != 'uuid'){
                        $table .= '<tr>'
                                . '<td>'.$testCaseID.'</td>'
                                . '<td>'.$data['duration'].'</td>'
                                . '<td>'.$data['outcome'].'</td>'
                                . '</tr>';
                    }
                }

                $table .= '</table>';

                 $form['exportResults']['testCasesToExport'] = array(
                        '#type' => 'item',
                        '#title' => t('Test Cases to Export'),
                        '#markup' => $table,
                    );
                 
            }else{
                $form['exportResults']['Heading'] = array(
                    '#type' => 'item',
                    '#title' => t(''),
                    '#markup' => '<h1>Modify Test Case Identifier</h1>',
                );
                foreach($testResData as $testCaseID => $data){
                  //  drupal_set_message('<pre>' . print_r($data, 1) . '</pre>');
                    if($testCaseID != 'uuid'){
                        
                        $form['exportResults']['changeTest_'.$testCaseID] = array(
                            '#type' => 'textfield',
                            '#title' => t('Test Case - '.$testCaseID),
                            '#default_value' => $testCaseID,
                        );
                    }
                }
                
            }
            
            $form['exportResults']['back'] = array(
                '#type' => 'submit',
                '#value' => t('Back'),
            );
                 
            $form['exportResults']['exportxls'] = array(
                '#type' => 'submit',
                '#value' => t($submitButtonExcel),
            );

//            $form['exportResults']['exportcsv'] = array(
//                '#type' => 'submit',
//                '#value' => t($submitButtonCSV),
//            );
        break;
      
        
  }

  
 
      
     return $form;
}

function reportsuite_exportResults_form_submit($form, &$form_state)
{
    
    if ($form_state['clicked_button']['#value'] == 'Next') {
       
        
        if(isset($form_state['values']['pathToTestCases'])){
            $form_state['storage']['testCasePath'] = 
                    str_replace(" ", "", $form_state['values']['pathToTestCases']);
        }
        
        $form_state['storage']['changedTestCaseID'] = false;
//        if($form_state['storage']['steps'] == 3){
//            foreach($form_state['values'] as $key => $values){
//                if(preg_match("/^changeTest_/", $key)){
//                    $form_state['storage']['changedTestCaseID'] = true;
//                    $form_state['storage'][$key] = $values;
//                }
//            }
//        }
        
        if($form_state['values']['testCaseSynced'] == 'no'){
            $form_state['storage']['changedTestCaseID'] = true;
        }
        
        $form_state['storage']['steps']++;
        $form_state["rebuild"] = TRUE;
    }else if ($form_state['clicked_button']['#value'] == 'Back') {
        $form_state['storage']['steps']--;
        $form_state["rebuild"] = TRUE;
    }else{
        $reid = $_SESSION['exportResults']['reid'];
        
        $xmlObj = reportsuiteXmlProcessor::processTestResultsXml($reid, true);
        $testResData = reportsuiteXmlProcessor::
                processXmlIntoTestCasesResultData($xmlObj);
        
        $testResDataReplace = array();
        if($form_state['storage']['changedTestCaseID']){
            
            foreach($form_state['values'] as $key => $value){
                if(preg_match("/^changeTest_/", $key)){
                    $newKey = str_replace('changeTest_', '', $key);
                    $testResDataReplace[$value] = $testResData[$newKey];
                }
            }
            unset($testResData);
            $testResData = $testResDataReplace;
        }
        $tpid = reportsuitequeries::getTpidByReid($reid);
        unset($_SESSION['exportResults']['reid']);
        
        reportsuiteresult::exportTestResults("excel", $testResData,
                $tpid, (String) $xmlObj->runBy, $form_state['storage']['testCasePath']);
    }
}