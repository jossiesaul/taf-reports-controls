<?php
/*
 *  * Reporting Suite Modules
 * 
 * @author Jossie Saul <jossie.saul@datalex.com>
 * 
 */

/**
 * 
 * @param type $graphName
 * @param Array $data this needs to be policed 
 * @return type
 */
function reportsuite_graphAndChart_switcher($graphName, $data = array())
{
    $graphChart = '<CENTER>ERROR: Graph/Chart Not Found</CENTER>';
    switch($graphName)
    {
        case 'historicalRun':
            $graphChart = reportsuite_historicalRun_chart($data['automationResultSet']);
            break;
        case 'historicalRunArea':
            $graphChart = reportsuite_historicalRun_areaChart($data['automationResultSet']);
            break;
        case 'historicalPassRate':
            $graphChart = reportsuite_historicalPassRate_chart($data['automationResultSet']);
            break;
        case 'historicalDuration':
            $graphChart = reportsuite_historicalDuration_chart($data['automationResultSet']);
            break;
        default:
            $graphChart = '<CENTER>ERROR: Graph/Chart Not Found</CENTER>';
            break;
    }
    return $graphChart;
}

function reportsuite_historicalRun_chart($automationResultSet)
{
    
    $chart = array(
      '#chart_id' => 'test_chart',
      '#title' => chart_title(t('Historical Run Chart'), 'cc0000', 15),
      '#type' => CHART_TYPE_LINE,
      '#size' => chart_size(950, 300),
      '#adjust_resolution' => TRUE,
    );
    
    $chart['#data'] = array('total' => array(),
                            'pass' => array(),
                            'fail' => array(),
                            'warning' => array(),
                            'tafError' => array(),
                      );
    
    $highestValue = 0;
    
    foreach($automationResultSet as $reid => $resultValue)
    {
        //drupal_set_message('<pre>'. print_r($resultValue, 1).'</pre>');
        array_push($chart['#data']['total'], $resultValue['Total']);
        array_push($chart['#data']['pass'], $resultValue['Pass']);
        array_push($chart['#data']['fail'], $resultValue['Fail']);
        array_push($chart['#data']['warning'], $resultValue['Warning']);
        array_push($chart['#data']['tafError'], $resultValue['TAFError']);
        
//        $runDateTime = date("F j, Y, g:i a", 
//                            strtotime(reportsuitequeries::getRunExecutionFieldQueryByReid($reid, 'run_datetime_start')));
        
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($reid);
        
        if($resultValue['Total'] > $highestValue) $highestValue = $resultValue['Total'];
        
       
    }
    //drupal_set_message('<pre>'. print_r($chart['#data'], 1).'</pre>');

    $chart['#legends'][] = t('Total Cases');
    $chart['#legends'][] = t('Pass');
    $chart['#legends'][] = t('Fail');
    $chart['#legends'][] = t('Warning');
    $chart['#legends'][] = t('TAF Error');

    $chart['#data_colors'][] = 'd0d0d0';
    $chart['#data_colors'][] = '00ff00';
    $chart['#data_colors'][] = 'ff0000';
    $chart['#data_colors'][] = 'ffa500';
    $chart['#data_colors'][] = '000000';

    
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, $highestValue);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label(t('Cases'), 50);


    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Run ID'), 50);

    //drupal_set_message('<pre>'.  print_r($chart, 1).'</pre>');
    
    return theme('chart', array('chart' => $chart));
}

function reportsuite_historicalPassRate_chart($automationResultSet)
{
    $chart = array(
      '#chart_id' => 'test_chart',
      '#title' => chart_title(t('Historical Pass Rate Chart'), 'cc0000', 15),
      '#type' => CHART_TYPE_BAR_V,
      '#size' => chart_size(950, 300),
      //'#adjust_resolution' => TRUE,
      '#grid_lines' => chart_grid_lines(30, 15),
      '#bar_size' => chart_bar_size(30, 5),
    );
    
    $chart['#data'] = array('passRateGreen' => array(),
                            'passRateAmber' => array(),
                            'passRateRed' => array(),
                      );
    
    $highestValue = 0;
    //drupal_set_message('<pre>'.  print_r($automationResultSet, 1).'</pre>');
    foreach($automationResultSet as $reid => $resultValue)
    {
        $passRate = (($resultValue['Pass']+$resultValue['Warning'])/$resultValue['Total'])*100;
                    
    
        if($passRate >= 80)
        {
            array_push($chart['#data']['passRateGreen'], (int) $passRate);
            array_push($chart['#data']['passRateAmber'], 0);
            array_push($chart['#data']['passRateRed'], 0);
        }
        else if($passRate >= 50 && $passRate < 80)
        {
            array_push($chart['#data']['passRateGreen'], 0);
            array_push($chart['#data']['passRateAmber'], (int) $passRate);
            array_push($chart['#data']['passRateRed'], 0);
        }   
        else if($passRate < 50)
        {
            array_push($chart['#data']['passRateGreen'], 0);
            array_push($chart['#data']['passRateAmber'], 0);
            array_push($chart['#data']['passRateRed'], (int) $passRate);
        }
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($reid);
        
        
       
    }
    //drupal_set_message('<pre>'. print_r($chart['#data'], 1).'</pre>');

    $chart['#legends'][] = t('PR% > 80%');
    $chart['#legends'][] = t('PR% > 50%');
    $chart['#legends'][] = t('PR% < 50%');
    
    $chart['#data_colors'][] = '00ff00';
    $chart['#data_colors'][] = 'ffa500';
    $chart['#data_colors'][] = 'ff0000';
    
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, 100);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label('Pass Rate %', 50);


    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Run ID'), 50);

    //drupal_set_message('<pre>'. print_r($chart, 1).'</pre>');
    return theme('chart', array('chart' => $chart));
}

function reportsuite_historicalDuration_chart($automationResultSet)
{
    
       $chart = array(
      '#chart_id' => 'test_chart',
      '#title' => chart_title(t('Historical Total Duration Chart'), 'cc0000', 15),
      '#type' => CHART_TYPE_LINE,
      '#size' => chart_size(950, 300),
      '#chart_fill' => chart_fill('c', 'eeeeee'),
      '#grid_lines' => chart_grid_lines(30, 15),
      '#adjust_resolution' => TRUE,
    );
    
    $chart['#data'] = array('duration' => array(),
                      );
    
    $highestValue = 0;
    //drupal_set_message('<pre>'. print_r($automationResultSet, 1).'</pre>');
    foreach($automationResultSet as $reid => $resultValue)
    {
        
        $totalDurationByXML = reportsuite::processTotalDurationBySuite(
                              reportsuiteXmlProcessor::processXmlIntoTestCasesResultData(
                              reportsuiteXmlProcessor::processTestResultsXml($reid)));
        //drupal_set_message('<pre>'. print_r($resultValue, 1).'</pre>');
        
        
        $totalDurByMin = number_format($totalDurationByXML/60, 0);
        //drupal_set_message($totalDurByMin);
        array_push($chart['#data']['duration'], $totalDurByMin);
        
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($reid);
        
        if($totalDurByMin > $highestValue) $highestValue = $totalDurByMin;
        
       
    }
    //drupal_set_message('<pre>'. print_r($chart['#data'], 1).'</pre>');

    $chart['#data_colors'][] = '000000';

    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, $highestValue);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label(t('Minutes'), 50);

    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Run ID'), 50);

    
    return theme('chart', array('chart' => $chart));
    
}

function reportsuite_overallCustomerRunProgressByDuration_chart($automationResultSet, $visibility = array(),
                $duration = array())
{
    
    if(!isset($duration))
    {
        $duration['from'] = date('Y-m-d');
        $duration['to'] = date('Y-m-d');
    }
    
    $chart = array(
      '#chart_id' => 'overallRunProgress',
      '#title' => chart_title(t('Overall Project Automation Run Progression | March 2013'), 'cc0000', 15),
      '#type' => CHART_TYPE_LINE,
      '#size' => chart_size(950, 300),
      '#adjust_resolution' => TRUE,
    );
    
    $chart['#data'] = array('total' => array(),
                            'pass' => array(),
                            'fail' => array(),
                            'warning' => array(),
                            'tafError' => array(),
                      );
    
    $highestValue = 0;
    
    $resValAddon = array('Total' => 0,
                                              'Passed' => 0,
                                              'Failed' => 0,
                                              'Warning' => 0,
                                              'TAFError' => 0,
                                 );
    
    foreach($automationResultSet as $reid => $resultValue)
    {
        
        $resValAddon['Total'] += $resultValue['CombineTotal']['Total'];
        $resValAddon['Passed'] += $resultValue['CombineTotal']['Passed'];
        $resValAddon['Failed'] += $resultValue['CombineTotal']['Failed'];
        $resValAddon['Warning'] += $resultValue['CombineTotal']['Warning'];
        $resValAddon['TAFError'] += $resultValue['CombineTotal']['TAFError'];
                
        
        array_push($chart['#data']['total'], $resValAddon['Total']);
        array_push($chart['#data']['pass'], $resValAddon['Passed']);
        array_push($chart['#data']['fail'], $resValAddon['Failed']);
        array_push($chart['#data']['warning'], $resValAddon['Warning']);
        array_push($chart['#data']['tafError'], $resValAddon['TAFError']);
        
        $date = date('jS M', strtotime($resultValue['additionalDets']['runDateTime']));
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($date);
        
        if($resValAddon['Total'] > $highestValue) $highestValue = $resValAddon['Total'];
        
       
    }
    //drupal_set_message('<pre>'. print_r($chart['#data'], 1).'</pre>');

    $chart['#legends'][] = t('Total Cases');
    $chart['#legends'][] = t('Pass');
    $chart['#legends'][] = t('Fail');
    $chart['#legends'][] = t('Warning');
    $chart['#legends'][] = t('TAF Error');

    $chart['#data_colors'][] = 'd0d0d0';
    $chart['#data_colors'][] = '00ff00';
    $chart['#data_colors'][] = 'ff0000';
    $chart['#data_colors'][] = 'ffa500';
    $chart['#data_colors'][] = '000000';

    
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, $highestValue);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label(t('Cases'), 50);


    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Run ID'), 50);

    //drupal_set_message('<pre>'.  print_r($chart, 1).'</pre>');
    
    return theme('chart', array('chart' => $chart));
}


function reportsuite_testCaseGrowth_chart($title, $tpid, $data)
{
    $chart = array(
      '#chart_id' => 'projectTestCasesGrowth-tpid-'.$tpid,
      '#title' => chart_title(t($title), 'cc0000', 15),
      '#type' => CHART_TYPE_LINE,
      '#size' => chart_size(600, 300),
      '#adjust_resolution' => TRUE,
    );
    
    $chart['#data'] = array('total' => array(),
                      );
    
    $highestValue = 0;
    
    $resValAddon = array('Total' => 0
                                 );
    
    foreach($data as $date => $noOfCases)
    {
        
        $resValAddon['Total'] += $noOfCases;
                
        array_push($chart['#data']['total'], $resValAddon['Total']);
        
        $date = date('jS M', strtotime($date));
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($date);
        
        if($resValAddon['Total'] > $highestValue) $highestValue = $resValAddon['Total'];
        
       
    }

    $chart['#legends'][] = t('Total Cases');
    $chart['#data_colors'][] = 'd0d0d0';

    
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, $highestValue);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label(t('# of Cases'), 50);


    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Date'), 50);

    //drupal_set_message('<pre>' . print_r($chart, 1) . '</pre>');
    
    return theme('chart', array('chart' => $chart));
}

function reportsuite_passToFailRatio_chart($tpid, $automationResultSet, $visibility = array(),
                $duration = array())
{
    
    if(!isset($duration))
    {
        $duration['from'] = date('Y-m-d');
        $duration['to'] = date('Y-m-d');
    }
    
    $chart = array(
      '#chart_id' => 'passToFailRatio-tpid'.$tpid,
      '#title' => chart_title(t('Pass to Fail Ratio'), 'cc0000', 15),
      '#type' => CHART_TYPE_BAR_V,
      '#size' => chart_size(800, 300),
      '#adjust_resolution' => TRUE,
      '#grid_lines' => chart_grid_lines(30, 15),
      '#bar_size' => chart_bar_size(30, 20),
    );
    
    $chart['#data'] = array('pass' => array(),
                            'fail' => array(),
                      );
    
    $highestValue = 0;
    
    $resValAddon = array('Passed' => 0,
                         'Passed' => 0,
                         'Failed' => 0,
                         );
    
    foreach($automationResultSet as $reid => $resultValue)
    {
        if(isset($resultValue['CombineTotal']))
        {
            $resValAddon['Passed'] = $resultValue['CombineTotal']['Passed']+
                                     $resultValue['CombineTotal']['Warning'];
            $resValAddon['Failed'] = $resultValue['CombineTotal']['Failed'];
        }       
        array_push($chart['#data']['pass'], $resValAddon['Passed']);
        array_push($chart['#data']['fail'], $resValAddon['Failed']);
        
        $date = date('jS M', strtotime($resultValue['additionalDets']['runDateTime']));
        $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][1][] = 
        chart_mixed_axis_label($date);
        
        $resValAddon['Total'] = $resValAddon['Passed']+$resValAddon['Failed'];
        
        if($resValAddon['Total'] > $highestValue) $highestValue = $resValAddon['Total'];
        
       
    }

    
    $chart['#legends'][] = t('Pass');
    $chart['#legends'][] = t('Fail');
    
    $chart['#data_colors'][] = '00ff00';
    $chart['#data_colors'][] = 'ff0000';
    
    
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][0][] = chart_mixed_axis_range_label(0, $highestValue);
    $chart['#mixed_axis_labels'][CHART_AXIS_Y_LEFT][1][] = chart_mixed_axis_label(t('# of Cases'), 50);


    $chart['#mixed_axis_labels'][CHART_AXIS_X_BOTTOM][2][] = chart_mixed_axis_label(t('Run Dates'), 50);

    //drupal_set_message('<pre>'.  print_r($chart, 1).'</pre>');
    
    return theme('chart', array('chart' => $chart));
}


function reportsuite_releasePhaseTracker_run_chart($tpid, 
        $automationResultStoreSortByTestCycle)
{
    
    if(!isset($duration))
    {
        $duration['from'] = date('Y-m-d');
        $duration['to'] = date('Y-m-d');
    }
    
    
    $chart['#data'] = array('total' => array(),
                            'pass' => array(),
                            'warning' => array(),
                      );
    
    $highestValue = 0;
    
    $resValAddon = array('Total' => 0,
                         'Passed' => 0,
                         'Warning' => 0,
                         'CombPassWarning' => 0,
                         );
    $i = 1;
    $gcDataTableCompile = '';
    $gcDataTableArr = array();
    foreach($automationResultStoreSortByTestCycle as $testCycle => 
            $automationResultStore)
    {
        $resValAddon = array('Total' => 0,
                         'Passed' => 0,
                         'Warning' => 0,
                         'CombPassWarning' => 0,
                         );
        foreach($automationResultStore as $reid => $resultValue)
        {

            if(isset($resultValue['CombineTotal']))
            {
                $resValAddon['Total'] += $resultValue['CombineTotal']['Total'];
                $resValAddon['Passed'] += $resultValue['CombineTotal']['Passed'];
                $resValAddon['Warning'] += $resultValue['CombineTotal']['Warning'];

                $resValAddon['CombPassWarning'] += 
                    $resultValue['CombineTotal']['Passed']+
                    $resultValue['CombineTotal']['Warning'];

                $date = date('jS M', strtotime(
                            $resultValue['additionalDets']['runDateTime']));

                if($resValAddon['Total'] > $highestValue) 
                        $highestValue = $resValAddon['Total'];

                if(!empty($gcDataTableArr[$testCycle][$date]['Total']))
                    $gcDataTableArr[$testCycle][$date]['Total'] + $resValAddon['Total'];
                else
                    $gcDataTableArr[$testCycle][$date]['Total'] = $resValAddon['Total'];
                
                if(!empty($gcDataTableArr[$testCycle][$date]['CombPassWarning']))
                    $gcDataTableArr[$testCycle][$date]['CombPassWarning'] + 
                                $resValAddon['CombPassWarning'];    
                else
                    $gcDataTableArr[$testCycle][$date]['CombPassWarning'] = 
                                $resValAddon['CombPassWarning'];
            }
        }
    }

    
    //GOOGLE CHART MultiDim Array into GoogChart DataTable Formatting 
    //Algoritm
    $gcDataTableHeader = '["x", "Total Case Ran", ';
    $gcDataTableBody = '';
    //NOTE!
    //Change Offset to -1 since static column 'Total' is given
    $currentColumnOffset = 0;
    $totalNoOfTestCycle = count($gcDataTableArr);
    foreach($gcDataTableArr as $testCycle => $data)
    {
        $currentColumnOffset++;
        $gcDataTableHeader .= '"'.$testCycle.' - Passes", ';
        foreach ($data as $date => $result)
        {
            $gcDataTableBody .= '["'.$date.'", '.$result['Total'].', ';
            if(!isset($lastColumnNumber))
            {
                $gcDataTableBody .= $result['CombPassWarning'];
                for ($i = 1; $i <= ($totalNoOfTestCycle-$currentColumnOffset); $i++) 
                {
                    $gcDataTableBody .= ', 0';
                }
            }
            else
            {
                for ($i = 1; $i <= ($currentColumnOffset-1); $i++) 
                {
                    $gcDataTableBody .= '0, ';
                }
                $gcDataTableBody .= $result['CombPassWarning'];
                for ($i = 1; $i <= ($totalNoOfTestCycle-$currentColumnOffset); $i++) 
                {
                    $gcDataTableBody .= ', 0';
                }
            }
            $gcDataTableBody .= '],';
        }
        $lastColumnNumber = $currentColumnOffset;
    }
    $gcDataTableHeader .= '],';
    
    
    $gcDataTable = "[
          ".$gcDataTableHeader."
          ".$gcDataTableBody."
        ]";
    
    $googlejs = "
 google.load('visualization', '1', {packages: ['corechart']});
 function drawVisualization() {
        // Create and populate the data table.
        var data = google.visualization.arrayToDataTable(".$gcDataTable.");
      
        // Create and draw the visualization.
        new google.visualization.AreaChart(document.getElementById('rptr-tpid".$tpid."')).
            draw(data, {curveType: \"function\",
                        width: 800, 
                        height: 400,
                        vAxis: {maxValue: 10},
                        animation: {easing: 'linear'}
                        }
                );
      }
      
      google.setOnLoadCallback(drawVisualization);
";
 
    
    drupal_add_js($googlejs, 'inline');
    
    
    return '<div id="rptr-tpid'.$tpid.'" style="width: 800px; height: 400px;"></div>';
}


function reportsuite_releasePhaseTracker_caseGrowth_chart($tpid, 
        $growthByReleasePhase)
{
    
    //drupal_set_message('<pre>' . print_r($growthByReleasePhase, 1) . '</pre>');
    
    $i = 1;
    //$gcDataTableCompile = '[["x", "Cases", null],';
    $gcDataTableCompile = "data.addColumn('string', 'Release Phase');
                           data.addColumn('number', 'Cases');
                          ";
    $accumulationOfCases = 0;
    
    $gcDataTableCompile .= 'data.addRows([';
    foreach ($growthByReleasePhase as $testCycle => $cases)
    {
        $accumulationOfCases += $cases;
        $gcDataTableCompile .= '["'.$testCycle.'",
                                  '.$accumulationOfCases.'
                                ],';
    }
    $gcDataTableCompile .= ']);';
   
    
    $googlejs = "
 google.load('visualization', '1', {packages: ['corechart']});
 function drawVisualization() {
        // Create and populate the data table.
        var data = new google.visualization.DataTable();
        ".$gcDataTableCompile."
      
        // Create and draw the visualization.
        new google.visualization.AreaChart(document.getElementById('rptcg-tpid".$tpid."')).
            draw(data, {curveType: \"function\",
                        width: 800, 
                        height: 400,
                        vAxis: {maxValue: 10},
                        animation: {easing: 'linear'}
                        }
                );
      }
      
      google.setOnLoadCallback(drawVisualization);
";
 
    
    drupal_add_js($googlejs, 'inline');
    
    
    return '<div id="rptcg-tpid'.$tpid.'" style="width: 800px; height: 400px;"></div>';
}

function reportsuite_resultOutcomePieChart($resData)
{
    $chart = array(
      '#chart_id' => 'run_result_outcome',
      '#title' => t(''),
      '#type' => CHART_TYPE_PIE_3D,
      '#size' => chart_size(400, 200),
      '#adjust_resolution' => TRUE,
      );

    $chart['#data']['passed'] = $resData['passed'];
    $chart['#data']['warning']  = $resData['warning'];
    $chart['#data']['failed']  = $resData['failed'];
    $chart['#data']['error']  = $resData['error'];
    
    
    $chart['#legends'][] = t('Passed');
    $chart['#legends'][] = t('Warning');
    $chart['#legends'][] = t('Failed');
    $chart['#legends'][] = t('TAF Error');
    
    $chart['#data_colors'][] = '00ff00';
    $chart['#data_colors'][] = 'ffa500';
    $chart['#data_colors'][] = 'ff0000';
    $chart['#data_colors'][] = '000000';
    
    return theme('chart', array('chart' => $chart));
}

function reportsuite_historicalRun_areaChart($automationResultSet)
{
    $header = "['Run ID', 'TAF Error', 'Warning', 'Failed', 'Passed'],";
    $data = '';
    foreach ($automationResultSet as $reid => $value)
    {
        $data .= "['REID-".$reid."', ".$value['TAFError'].", ".$value['Warning'].", ".$value['Fail'].", ".$value['Pass']."],";
    }
    
    $gcDataTable = "[".$header.$data."]";
    
    $googlejs = " google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(".$gcDataTable .");

        var options = {
          title: 'Automation Run Chart',
          hAxis: {title: 'Run Execution ID',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0},
          isStacked: true,
          colors: ['black', 'yellow', 'red', 'green']
        };

        var chart = new google.visualization.AreaChart(document.getElementById('summaryAreaChart'));
        chart.draw(data, options);
      }";
    
    drupal_add_js($googlejs, 'inline');
    
    return '<div id="summaryAreaChart" style="width: 950px; height: 400px;"></div>';
}

function reportsuite_allAutoTimeSaving_barChart($automationResultSet, $titleExtension)
{
 
    $header = "['Projects', 'Hours', { role: \"style\" }],";
    $data = '';
    foreach($automationResultSet as $tpid => $value)
    {
        $timeSaved = !empty($value['analyticalData']['totalDurationTime'])?
                        reportsuite::processTotalDurationBySeconds($value['analyticalData']['totalDurationTime'], 3, true):0;
        
        $data .= "['".$value['tpData']['CustomerAlias']."', ".floatval($timeSaved).", \"blue\"],";
    }
    $gcDataTable = "[".$header.$data."]";
    $googlejs = " google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable(".$gcDataTable .");

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: \"stringify\",
                         sourceColumn: 1,
                         type: \"string\",
                         role: \"annotation\" },
                       2]);

      var options = {
        title: \"Automation Project Time Savings - ".$titleExtension."\",
        width: 900,
        height: 1500,
        bar: {groupWidth: \"60%\"},
        chartArea: {height: '90%'},
        legend: { position: \"none\" },
      };
      var chart = new google.visualization.BarChart(document.getElementById(\"mpBarChart\"));
      chart.draw(view, options);
  }";
    
    drupal_add_js($googlejs, 'inline');
    
    return '<div id="mpBarChart" style="width: 950px; height: 1500px;"></div>';
}

?>
    