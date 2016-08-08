<?php print $data['links']['sublinks']; ?>

<? print $data['links']['pageLinks'];?>


<?if(isset($data['testcaseGrowth'])):?>
<div>
    <h2><b>Test Case Growth</b></h2>
    
    <h3>
    Total Number Of Automated Test Cases: <? print $data['testcaseGrowth']['total']['testCases'];?>
    </h3>
    
    <div>
        <center>
        <? print $data['testcaseGrowth']['chart']['testCaseGrowth'];?>
        </center>
    </div>
    
    <table>
        <tr>
        <th colspan="2">Automated Test Growth History Table</th>
        </tr>
        <tr>
            <th>Date</th><th># of Cases</th>
        </tr>
        <? foreach($data['testcaseGrowth']['stats'] as $date => $num):?>
        <tr>
            <td><?print date("F j, Y", strtotime($date));?></td>
            <td><?print $num;?></td>
        </tr>
        <?  endforeach;?>
    </table>
    
</div>

<?else: ?>
<!--////////////////////////////////////////-->
<!--   STATISTICAL SUMMARY -->
<!--////////////////////////////////////////-->
<div>
<? if(isset($data['analysis']['statistical']['chart']['testRuns']))
    print $data['analysis']['statistical']['chart']['testRuns'];?>
</div>

<div>
    <? if(!empty($data['links']['rpArr'])):?>
    <h2><u><b>Release Phase:</b></u> <? print $data['analysis']['period'];?></h2>
    <br />
    <? foreach($data['links']['rpArr'] as $rpLinks): ?>
    <span>
            <? print $rpLinks; ?> 
    </span>
    <? if($rpLinks !== end($data['links']['rpArr'])) print '>>';?>
            
    <? endforeach;?>
            
    <? else: ?>
    <table>
<!--        <tr id="tblOverrideBg">
            <td style="text-align:center;" colspan="2">
                <? // print $data['link']['back']['summary'] ; ?>
            </td>
        </tr>-->
        <tr id="tblOverrideBg">
            <td style="text-align:left;">
              <?php print $data['links']['periodic']['prev']; ?>
            </td>
            <td style="text-align:right;">
              <?php print $data['links']['periodic']['next']; ?>
            </td>
        </tr>
    </table>
    <h1>
        <b>Period [<? print $data['analysis']['period'];?> Report]:</b>
    <? print date("F j, Y", strtotime($data['analysis']['timespan']['from']));?>
    -
    <? print date("F j, Y", strtotime($data['analysis']['timespan']['to']));?>
    </h1>
    <? endif;?>

</div>

<div id="analysisTabs">
    <ul>
        <li><a href="#analysisTabs-1">Stats Summary</a></li>
        <li><a href="#analysisTabs-2">Test Run Results Table</a></li>
        <li><a href="#analysisTabs-3">Error Stats Summary</a></li>
        <li><a href="#analysisTabs-4">Test Case Result History</a></li>
        <li><a href="#analysisTabs-5">Test Case Growth</a></li>
    </ul>
    <div id="analysisTabs-1">
       <? if(!isset($data['analysis']['testResults']['data']) && empty($data['analysis']['testResults']['data'])): ?>
        <p class="noticeTextRed">No Automation Ran for this period</p>
        <? else:?>
        <div class="container_12">
            <div class="grid_3">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Total Number Test Run</th>
                    </tr>
                    <tr>
                        <td>
                            <span style="font-size: 30px; font-weight: bolder;">
                            <? print reportsuitehlanalysis::
                                    countTotalNumberOfCasesRun($data['analysis']['testResults']['data'])?>
                            </span>
                        </td>
                    </tr>
<!--                    <tr>
                        <th>Total Executed ActionStep</th>
                    </tr>
                    <tr>
                        <td>
                            <? // print array_sum($data['analysis']['statistical']['actionSteps']['total']);?>
                        </td>
                    </tr>-->
                </table>
            </div>
            <div class="grid_4">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Average Test Run PassRate <?print $data['notices']['popup']['averagePrDesc'];?></th>
                    </tr>
                    <tr>
                        <td>
                            <span class="passRateEyeCatchDp">
                            <? print reportsuite::addVisiualEffectOnPR( reportsuitehlanalysis::
                                    getAveragePassRateFromTestRunResults($data['analysis']['testResults']['data']).'%');?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Average ActionSteps PassRate</th>
                    </tr>
                    <tr>
                        <td>
                            <span class="passRateEyeCatchDp">
                            <? print reportsuitequeries::calculatePassRate(
                                    array_sum($data['analysis']['statistical']['actionSteps']['total']), 
                                    $data['analysis']['statistical']['actionSteps']['total']['Passed'], true)?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="grid_4">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th colspan="3">
                            Total Result Output 
                            (<? print array_sum($data['analysis']['testResults']['totalOutcome']);?>
                            Test Cases)<br />
                        </th>
                    </tr>
                    <tr>
                    <?  foreach ($data['analysis']['testResults']['outputCriteria'] as $oc):?>
                        <th>
                            <? print $oc;?>
                        </th>
                    <? endforeach;?>
                    </tr>
                    <tr>
                    <?  foreach ($data['analysis']['testResults']['outputCriteria'] as $oc):?>
                        <td id='<? print 'res'.$oc.'TextValue';?>'>
                            <? print $data['analysis']['testResults']['totalOutcome'][$oc];?>
                        </td>
                    <? endforeach;?>
                    </tr>
                    <tr>
                        <th style="text-align: left;">Total <br />PassRate:</th>
                        <td colspan="2" >
                            <span style="font-size: 30px; font-weight: bolder;">
                                <? print reportsuitequeries::calculatePassRate(
                                    array_sum($data['analysis']['testResults']['totalOutcome']), 
                                    $data['analysis']['testResults']['totalOutcome']['Passed']+
                                    $data['analysis']['testResults']['totalOutcome']['Warning'], true)?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="grid_6">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Manual Effort Time Saved</th>
                    </tr>
                    <tr>
                        <td>
                            <span class="EyeCatchDpText">
                            <? print reportsuite::processTotalDurationBySeconds(
                                    $data['analysis']['statistical']['duration']['single'], 3, true)?>
                           </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="grid_5">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Concurrent RunTime(GRID) Total Duration</th>
                    </tr>
                    <tr>
                        <td>
                            <span class="EyeCatchDpText">
                            <? 
                                    if($data['analysis']['statistical']['duration']['grid'] !== 0):
                                        print reportsuite::processTotalDurationBySeconds(
//                                        $data['analysis']['statistical']['duration']['single'] -
                                                $data['analysis']['statistical']['duration']['grid'], 3, true);
                             ?>
                            </span>   
                             <?else:?>
                            <span class="noticeTextRed">Did not use GRID runTime for <? print $data['analysis']['period'];?> period</span>
                             <?  endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="grid_6">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Top Runners</th>
                        <th># Ran</th>
                    </tr>
                    
                    <? arsort($data['analysis']['statistical']['runners']);
                    foreach($data['analysis']['statistical']['runners'] as $runBy => $num):?>
                    <tr>
                        <td><? print $runBy;?></td>
                        <td><? print $num; ?></td>
                    </tr>
                    <?  endforeach;?>
                </table>
            </div>
            <div class="grid_5">
                <table class="statSummaryTablesOnGrid">
                    <tr>
                        <th>Environment</th>
                        <th># Ran</th>
                    </tr>
                   <? arsort($data['analysis']['statistical']['environments']);
                    foreach($data['analysis']['statistical']['environments'] as $env => $num):?>
                    <tr>
                        <td><? print l($env,$env);?></td>
                        <td><? print $num; ?></td>
                    </tr>
                    <?  endforeach;?>
                </table>
            </div>
        </div>
        <?  endif;?>
        
    </div>
    <div id="analysisTabs-2">
    <? if(isset($data['analysis']['testResults']['data']) && !empty($data['analysis']['testResults']['data'])): ?>
        <table>
            <tr>
                <th>Run ID</th>
                <th>Environment</th>
                <th>Scenario(s)</th>
                <th>Pass Rate</th>
                <th>Total Cases</th>
                <th>Passed/ Warning/ Failed</th>
                <th>Test Cycle</th>
                <th>Duration</th>                
                <th>Run Date</th>
            </tr>

        <? ksort($data['analysis']['testResults']['data']);
            foreach($data['analysis']['testResults']['data'] as $reid => $trdata):?>
            <tr>
                <td><? print l($reid, 'reportsuite/resultDetail/'.$reid.'/null/null'); ?></td>
                <td><? print $trdata['additionalDets']['testEnvironment'];?></td>
                <td>
                    <? foreach ($trdata as $scenario => $sdt){
                        if(!in_array($scenario, array('CombineTotal','additionalDets'))) print $scenario.'<br />';
                    }?>

                </td>
                <td id="prOnTable">
                    <? print reportsuitequeries::calculatePassRate(
                            $trdata['CombineTotal']['Total'], $trdata['CombineTotal']['Total Passed'], true)?>
                </td>
                <td><? print $trdata['CombineTotal']['Total']; ?></td>
                <td>
                    <span id='resPassedTextValue'>
                    <? print $trdata['CombineTotal']['Passed']; ?>
                    </span>/
                    <span id='resWarningTextValue'>
                    <? print $trdata['CombineTotal']['Warning']; ?>
                    </span>/
                    <span id='resFailedTextValue'>
                    <? print $trdata['CombineTotal']['Failed']; ?>
                    </span>
                </td>
                <td>
                    <? print reportsuite::$testCycleDefinition[$trdata['additionalDets']['testCycle']];?>
                </td>
                <td>
                    <?if(isset($trdata['additionalDets']['gridDurationInSec'])){
                        print reportsuite::processTotalDurationBySeconds(
                                $trdata['additionalDets']['gridDurationInSec'],4).
                                " <br />(&#9733;GRID Run)";
                    }else{
                        print reportsuite::processTotalDurationBySeconds(
                                $trdata['additionalDets']['durationInSec'],4);
                    }
                    ?>
                </td>
                <td>
                    <?
                    print date("j M, Y,", strtotime($trdata['additionalDets']['runDateTime'])).'<br />'.
                                date("g:i a", strtotime($trdata['additionalDets']['runDateTime']));
                    ?>
                </td>
            </tr>
        <? endforeach; ?>
        </table>

     <?else:?>
        <p class="noticeTextRed">No Automation Ran for this period</p>
     <? endif; ?>
    </div>
    <div id="analysisTabs-3">
        
        <? if(isset($data['analysis']['testResults']['data']) && !empty($data['analysis']['testResults']['data'])): ?>
        <table>
                <tr>
                    <th>Failure Commonality</th>
                    <th># Occurrence</th>
                </tr>
                <? foreach(reportsuiteanalysishelper::
                                         sortArrayOnCounterElement(
                                         $data['analysis']['statistical']['failureCommonality'], SORT_DESC) as 
                                      $failureCommonality):
                 ?>
                <tr>
                    <td><? print  str_replace("::", "<br /><br />", $failureCommonality['errMsg']);?></td>
                    <td><? print $failureCommonality['counter'];?></td>
                </tr>
                <? endforeach;?>
            </table>
        <?else:?>
        <div>No Data</div>
        <?  endif;?>
    </div>
    <div id="analysisTabs-4">
        <? if(isset($data['analysis']['testResults']['data']) && !empty($data['analysis']['testResults']['data'])): ?>
            <table>
                <tr>
                    <th colspan="<? print count(array_keys($data['analysis']['testResults']['data']))+1;?>">
                    <h2>Test Case Result History Table</h2>
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <? foreach(array_keys($data['analysis']['testResults']['data']) as $reid):?>
                    <th><?print $reid;?></th>
                    <? endforeach;?>
                </tr>
                <?$newRow = false;
                    foreach($data['analysis']['statistical']['testCaseResultHistory'] as $tcId => $reidSet):?>
                <tr>
                    <td><? print $tcId;?></td>
                    <? foreach(array_keys($data['analysis']['testResults']['data']) as $reid):
                        if(in_array($reid, array_keys($reidSet))){
                            $cssAddon = 'res'.$reidSet[$reid]['outcome'].'BgHighlight';
                            $tcResult = $reidSet[$reid]['outcome'];
                        }else{
                            $cssAddon = '';
                            $tcResult = 'NA';
                        }
                        ?>
                        <td id="<? print $cssAddon;?>">
                            <? print $tcResult;?>
                        </td>
                    <? endforeach;?>
                </tr>
                <?  endforeach;?>
            </table>
        <?else:?>
        <div>No Data</div>
        <?  endif;?>
    </div>
    <div id="analysisTabs-5">
        
        <?if(!isset($data['analysis']['testcaseGrowth']['total'])):?>
            <p>No new cases detected</p>
        <?else:?>
            <h3>
            Total <b>NEW</b> Number Of Automated Test Cases: <? print $data['analysis']['testcaseGrowth']['total'];?>
            </h3>
            <div>
                <center><? 
                if(isset($data['analysis']['statistical']['chart']['testCaseGrowth']))
                        print  $data['analysis']['statistical']['chart']['testCaseGrowth'];?>
                </center>
            </div>
            <? if($data['analysis']['testcaseGrowth']['total'] !== 0):?>
            <table>
                <tr>
                    <th>Test Case</th>
                    <th>Scenario</th>
                    <th># of ActionSteps</th>
                </tr>
            <?foreach($data['analysis']['testcaseGrowth']['list'] as $tcDt):?>
                <tr>
                    <td>
                        <h4><b><? print $tcDt['testcase_id'];?></b></h4>
                        <span><?print $tcDt['description'];?></span>
                    </td>
                    <td>
                        <?print $tcDt['scenario'];?>
                    </td>
                    <td>
                        <?print $tcDt['num_of_actionsteps'];?>
                    </td>
                </tr>
            <? endforeach;?>
            </table>
            <? endif;?>
        <?  endif;?>
    </div>
</div>
<? endif;?>