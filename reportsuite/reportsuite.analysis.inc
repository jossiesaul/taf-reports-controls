<?php

/**
*
 * @author Jossie Saul <jossie.saul@datalex.com> 
 */


function reportsuite_renderDisplayOverallAnalysis($projectDataAnalysis, $dept)
{
    $renderedDataDisplay = array();
    foreach($projectDataAnalysis as $tpid => $data)
    {
         $projectOverview = '';
        //reportsuite::listOfTestCases($tpid);
        //drupal_set_message('<pre>' . print_r($data, 1) . '</pre>');
        $projectOverview .= '<center><h3>High Level Detail</h3></center>';
        $projectOverview .= '<table>';
        $projectOverview .= '<tr>';
        $projectOverview .= '<th style="text-align:left;">Total Test Cases
                             <button id="opener-tpid'.$tpid.'" 
                             style="font-size:10px;" >
                             List
                             </button>
                             </th>';
        $projectOverview .= '<td style="text-align:center;">'.$data['totalNumOfTestCases'].'</td>';
        $projectOverview .= '</tr>';
        $projectOverview .= '<tr>';
        $projectOverview .= '<th style="text-align:left;">Total <b>Active</b> Test Cases</th>';
        $projectOverview .= '<td style="text-align:center;">'.$data['totalNumOfTestCases'].'</td>';
        $projectOverview .= '</tr>';
        $projectOverview .= '<tr>';
        $projectOverview .= '<th style="text-align:left;">Last Run Pass Rate<br />';
        $projectOverview .= '<button>';
        $projectOverview .= l('Click for Last Run Result', 
                                'reportsuite/latestRun/'.
                                $data['projectInfo']['Customer'].'/'.
                                $data['projectInfo']['POS'].'/'.
                                $dept, 
                                array('attributes' => 
                                    array('target' => '_blank', 
                                          'style' => 'font-size:10px;font-weight:normal;',
                                        )));
        $projectOverview .= '</button>';
        $projectOverview .= '</th>';
        $projectOverview .= '<td style="text-align:center;">';
        $projectOverview .= $data['lastRunPassRate'];
        $projectOverview .= '</td>';
        $projectOverview .= '</tr>';
        $projectOverview .= '<tr>';
        $projectOverview .= '<th style="text-align:left;">Average Pass Rate</th>';
        $projectOverview .= '<td style="text-align:center;">'.$data['averagePassRate'].'</td>';
        $projectOverview .= '</tr>';
        $projectOverview .= '</table>';
        
        
        $projectOverviewTCGChart = reportsuite_testCaseGrowth_chart(
                                   'Automated Cases Growth', 
                                   $tpid,
                                   $data['growthOfTestCases']);
        
        $duration = array('from' => '2013-03-01', 'to' => '2013-03-30');
        $automationResultStore = reportsuitehlanalysis::
                                 getAllTestCaseResultsDataByTimeSpan(
                                 $tpid, $dept, $duration);
        
        $automationResultStoreAll = reportsuitehlanalysis::
                getAllTestCasesResultsDataToDate($tpid, $dept);
        
        
        //drupal_set_message('<pre>' . print_r($automationResultStoreFull, 1) . '</pre>');
        
        $projectOverviewPFRChart = reportsuite_passToFailRatio_chart($tpid, $automationResultStore);
        $projectOverviewRPTRChart = reportsuite_releasePhaseTracker_run_chart($tpid, 
                reportsuiteanalysis::sortAutoResultStoreByTestCycle($automationResultStoreAll));
        
        
        reportsuite::getReleasePhaseDates($automationResultStoreAll);
        $projectOverviewRPTCGChart = reportsuite_releasePhaseTracker_caseGrowth_chart($tpid,
                reportsuiteanalysis::getGrowthOfTestCasesOnProjectReleasePhase($tpid, 
                reportsuite::getReleasePhaseDates($automationResultStoreAll)));
        
        $renderedDataDisplay[$tpid]['table'] = $projectOverview;
        $renderedDataDisplay[$tpid]['chart']['acg'] = $projectOverviewTCGChart;
        $renderedDataDisplay[$tpid]['chart']['pfr'] = $projectOverviewPFRChart;
        $renderedDataDisplay[$tpid]['chart']['rptr'] = $projectOverviewRPTRChart;
        $renderedDataDisplay[$tpid]['chart']['rptcg'] = $projectOverviewRPTCGChart;
    }
    //drupal_set_message('<pre>' . print_r($renderedDataDisplay, 1) . '</pre>');
   return $renderedDataDisplay;
}

function reportsuite_renderCoverageOverallOnProject($compiledListOfFeatureCoverage)
{
    
    $renderedDataDisplay = array();
    
    foreach($compiledListOfFeatureCoverage as $tpid => $coverageInfo)
    {
        $coverageOverview = '';
        //drupal_set_message('<pre>' . print_r($coverageInfo, 1) . '</pre>');
        $coverageOverview .= '<center><h3>Feature Coverage Table</h3></center>';
        $cHeading = '';
        $cList = '';
        $coverageCompile = array();
        foreach($coverageInfo as $scenario => $testCase)
        {
            foreach($testCase as $coverage)
            {
                foreach($coverage as $coverType => $cover)
                {
                    if(!isset($coverageCompile[$coverType]))
                    {
                        $coverageCompile[$coverType] = array();
                    }
                        
                    foreach ($cover as $v)
                    {
                        if(!in_array($v, $coverageCompile[$coverType]))
                        {
                            $coverageCompile[$coverType][] = $v;
                        }
                    }   
                }
            }
        }
        
        foreach($coverageCompile as $coverType => $cover)
        {
            $cHeading .= '<th>'.$coverType.'</th>';
            $cList .= '<td>';
            foreach($cover as $v)
            {
                $cList .= $v.'<br />';
            }
            $cList .= '</td>';
        }

        $coverageOverview .= '<table>';
        $coverageOverview .= '<tr>'.$cHeading.'</tr>';
        $coverageOverview .= '<tr>'.$cList.'</tr>';
        $coverageOverview .= '</table>';
        $renderedDataDisplay[$tpid]['coverage'] = $coverageOverview;
        
    }
    return $renderedDataDisplay;
}

function reportsuite_projectAutomationAnalysis($tpid, $dept, $duration)
{
    return reportsuite_highLevelProjectAnalysis($tpid, $dept);
}

function reportsuite_highLevelProjectAnalysis($tpid, $dept)
{
    $output = '';
    
    $duration = array('from' => '2013-03-01', 'to' => '2013-03-25');
    
    $page = 2;
    
    if($page == 1)
    {
        $automationResultStore = reportsuitehlanalysis::getAllTestCaseResultsDataByTimeSpan(
                                                       $tpid, $dept, $duration);

        drupal_set_message('<pre>' . print_r($automationResultStore, 1) . '</pre>');

        $output = reportsuite_overallCustomerRunProgressByDuration_chart($automationResultStore, 
                          array('Total' => true, 'Pass' => true, 'Warning' => true, 'Fail' => true), 
                          $duration);
    }
    
    
    if($page == 2)
    {
        $automationResultStore = reportsuitehlanalysis::
                                      compileAllProjectAutomationTestCaseResultByTimeSpan($dept, $duration);
           $output = reportsuite_overallCustomerRunProgressByDuration_chart($automationResultStore, 
                      array('Total' => true, 'Pass' => true, 'Warning' => true, 'Fail' => true), 
                      $duration);
           
    }
}

