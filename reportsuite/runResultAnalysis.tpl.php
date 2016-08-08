<?php print $data['links']['sublinks']; ?>

<h2><?print $data['title']['addon'];?> Analysis</h2>
<br />
<div>
    <?php print $data['links']['summary']; ?>
    
    <?php print $data['links']['detailed']; ?>
</div>

<div>
    <table class="resultTable analysisSummary" style="width:100%;">
        <tr>
            <th colspan='3'>Test Cases Pass Rate</th>
            <th colspan='3'>ActionSteps Pass Rate</th>
        </tr>
        <tr>
            <td colspan='3' id="rrSummShow"><? print reportsuitequeries::calculatePassRate(
                    $data['run']['results']['totalTestCases'], 
                    $data['run']['results']['passed']+$data['run']['results']['warning'], true)?></td>
            <td colspan='3' id="rrSummShow"><? print reportsuitequeries::calculatePassRate(
                    $data['run']['statistics']['total']['actionStepsStats']['totalNumOfSteps'], 
                    $data['run']['statistics']['total']['actionStepsStats']['numOfStepsPassed'],true);?></td>
        </tr>
        <tr>
            <?for($i=1;$i<=2;$i++):?>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?>
            <th><?print $rc?></th>
            <? endforeach;?>
            <? endfor;?>
        </tr>
        <tr>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?> 
            <td id="<?print 'res'.$rc.'TextValue';?>"><? print $data['run']['results'][strtolower($rc)]?></td>
            <? endforeach;?>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?> 
           <td id="<?print 'res'.$rc.'TextValue';?>"><? print $data['run']['statistics']['total']['actionStepsStats']['numOfSteps'.$rc]?></td>
           <? endforeach;?>
        </tr>
        <tr>
            <th colspan="6">Manual Effort</th>
        </tr>
        <tr>
            <td colspan="6" id="rrSummShow">
                <? print reportsuite::processTotalDurationBySeconds(
                                   $data['run']['statistics']['total']['duration']['total'],4);?>
                SAVED
            </td>
        </tr>
        <?  if($data['run']['variables']['isGrid']): ?>
            <tr>
            <th colspan="6">Grid RunTime</th>
            </tr>
            <tr>
                <td colspan="6" id="rrSummShow">
                <? print reportsuite::processTotalDurationBySeconds(
                        $data['run']['statistics']['total']['duration']['total'] - $data['run']['results']['grtDuration'], 4);?>
                    SAVED
                </td>
            </tr>
        <? endif; ?>
    </table>

</div>

<? foreach($data['run']['statistics'] as $statsKey => $statsValue): ?>

<? $asHeading = $statsKey == 'total'?'Overall Run Analysis':'<b>Test Script:</b> '.$statsKey;?>
<? $hideStats = $statsKey == 'total'?'uncollapsed':'collapsed';?>
<fieldset class="collapsible <? print $hideStats;?>">
<legend><span class="fieldset-legend">
<a id="<? print $statsKey;?>"><? print $asHeading;?></a>
</span></legend>';
<div class="fieldset-wrapper analysisFieldset">

<h2><? print $asHeading;?></h2>

<h3 id="alysHeading">Scenario Variables</h3>
<div id="alysSegments">
    <? if(!empty($statsValue['searchType'])):?>
    <table class="resultTable">
        <tr>
            <th style="text-align: left;" id="alysTblHeading"
                colspan="<?print count(array_keys($statsValue['searchType']))*3;?>">
            <h2>Search Type</h2>
            </th>
        </tr>
        <tr>
        <? foreach($statsValue['searchType'] as $stKey => $stValue): ?>
            <th colspan="3"><? print array_sum($stValue).' '.$stKey; ?> Test Case(s)</th>
        <? endforeach; ?>    
        </tr>
        <tr>
        <? for($i=1; $i<=count($statsValue['searchType']);$i++): ?>
            <th>Passed</th><th>Warning</th><th>Failed</th>
        <? endfor;?>
        </tr>
        <tr>
    <? foreach($statsValue['searchType'] as $stKey => $stValue): ?>
            <td id="resPassedTextValue"><? print isset($stValue['Passed'])?$stValue['Passed']:0; ?></td>
            <td id="resWarningTextValue"><? print isset($stValue['Warning'])?$stValue['Warning']:0; ?></td>
            <td id="resFailedTextValue"><? print isset($stValue['Failed'])?$stValue['Failed']:0; ?></td>
    <? endforeach; ?>
        </tr>
    </table>
    <? endif; ?>
<hr>
    <? if(!empty($statsValue['ancillaries'])):?>
   <table class="resultTable">
        <tr>
            <th style="text-align: left;" id="alysTblHeading"
                colspan="<?print count(array_keys($statsValue['ancillaries']))*3;?>">
                <h2>Ancillaries</h2>
            </th>
        </tr>
        <tr>
        <? foreach($statsValue['ancillaries'] as $ancKey => $ancValue):?>
            <th colspan="3"><? print $ancValue['TotalTestCases'];?>
                Test Cases with <u><?print $ancKey?></u> purchase</th>
            
        <? endforeach;?>
        </tr>
        <tr>
            <?for($i=1;$i<=count($statsValue['ancillaries']);$i++):?>
                <? foreach($data['run']['resultVariable']['outputCriteria'] as $rHeading):?>   
                <th><? print $rHeading?></th>
                <? endforeach;?>
            <?endfor;?>
        </tr>
        <tr>
    <? foreach($statsValue['ancillaries'] as $ancKey => $ancValue): ?>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?>   
            <td id="<?print 'res'.$rc.'TextValue';?>">
                <? print isset($ancValue[$rc]['counter'])?$ancValue[$rc]['counter']:0; ?>
            </td>
            <? endforeach;?>
    <? endforeach; ?>
        </tr>
    </table>
    <? endif;?>
<hr>
    <? if(!empty($statsValue['paymentType'])):?>
    <table class="resultTable">
        <tr>
            <th style="text-align: left;" id="alysTblHeading"
                    colspan="<?print count(array_keys($statsValue['paymentType']))*3;?>">
            <h2>Payment Type</h2>
            </th>
        </tr>
        <tr>
    <?  foreach ($statsValue['paymentType'] as $ptk=>$ptv):?>
            <th colspan="3"><? print array_sum($ptv);?> Test Case(s) with <? print $ptk;?></th>
    <? endforeach; ?>
        </tr>
        <tr>
    <?  for($i=1; $i <= count($statsValue['paymentType']); $i++):?>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?>   
                <th><? print $rc;?></th>
            <? endforeach;?>
    <? endfor; ?>
        </tr>
        <tr>
            <?  foreach($statsValue['paymentType'] as $ptk=>$ptv):?>
                <? foreach($data['run']['resultVariable']['outputCriteria'] as $rc):?>   
                    <td id="<?print 'res'.$rc.'TextValue';?>">
                        <? print isset($ptv[$rc])?$ptv[$rc]:0;?>
                    </td>
                <? endforeach;?>
            <? endforeach; ?>
        </tr>
    </table>
    <? endif; ?>
    <hr>
    <? if(!empty($statsValue['routesUsage'])):?>
    <table class="resultTable">
            <tr>
                <th style="text-align: left;" id="alysTblHeading"
                        colspan="3">
                <h2>Routes Usage</h2>
                </th>
            </tr>
            <tr>
                <th >Passed</th>
                <th >Warning</th>
                <th >Failed</th>
            </tr>
          <?$ruData = reportsuiteanalysis::processRoutesUsageIntoTDS($statsValue['routesUsage']);?>
            <? for($i=0;$i<$ruData['rowToGenerate'];$i++):?>
            <tr>
            <? foreach($data['run']['resultVariable']['outputCriteria'] as $result):?>   
                <? if(isset($ruData[$result][$i])):?>
                    <td>
                        <p>
                        <?
                        $rStr = '';
                        foreach ($ruData[$result][$i]['routes'] as $rk=>$rv)
                        {
                            print $rk.': '.$rv.'<br />';
                            $rStr .= $rv.'';
                        }
                        ?>
                        (<?print $rStr;?> on <? print count($ruData[$result][$i]['tcs']);?> Test Cases)
                        </p>
                    
                        <ul id="<?print 'res'.$result.'TextHighlight';?>">
                        <? foreach($ruData[$result][$i]['tcs'] as $tcs):?>
                            <li><? print $tcs;?></li>
                        <? endforeach;?>
                        </ul>
                    </td>    
                <? else:?>
                    <td ></td>
                <? endif;?>
           <? endforeach;?>
           </tr>
          <? endfor;?>
        </table>
        <? endif;?>
</div>    
    
<h3>Flow Stats</h3>
<div id="alysSegments">
<table class="resultTable">
    <tr>
        <th style="text-align: left;" id="alysTblHeading"  colspan="3">
        <h2>Test Run Duration</h2>
        </th>
    </tr>
    <tr><th>Test Case Run</th><th>Time</th></tr>
    <tr>
        <td>Fastest</td>
        <td><? print reportsuite::processTotalDurationBySeconds(
                $statsValue['duration']['low'],4);?></td>
    </tr>
    <tr>
        <td>Mean</td>
        <td><? print reportsuite::processTotalDurationBySeconds(
                $statsValue['duration']['mean'],4);?></td>
    </tr>
    <tr>
        <td>Longest</td>
        <td><? print reportsuite::processTotalDurationBySeconds(
                $statsValue['duration']['high'],4);?></td>    
    </tr>
</table>
<hr>
<? if(isset($statsValue['pageFlow']['pageStats'])):?>
<table class="resultTable">
    <tr>
        <th style="text-align: left;" id="alysTblHeading"  
            colspan="<? print count(array_keys($statsValue['pageFlow']['pageStats']))?>">
        <h2>Page Hit PassRate</h2>
        </th>
    </tr>
    <tr>
    <?foreach($statsValue['pageFlow']['pageStats'] as $pgsKey=> $pgsValue): ?>
        <?
        $pgsPassed = isset($pgsValue['Passed'])?$pgsValue['Passed']:0;
        $pgsFailed = isset($pgsValue['Failed'])?$pgsValue['Failed']:0;
        ?>
        <td>
            <h3><?print $pgsKey?></h3>
            <? print reportsuitequeries::calculatePassRate(
                    $pgsPassed+$pgsFailed, $pgsPassed, true)?>
            <br />
            <? if($pgsKey == 'pageAdvancedSearch'):
                print '(';
                print isset($pgsValue['NA'])?$pgsValue['NA']:0;
            else:?>
            (
            <span id="resPassedTextValue">
            <? print  $pgsPassed; ?>
            </span>
            <? endif; ?>
            /
            <span id="resFailedTextValue">
            <? print $pgsFailed ?>
            </span>
            )
            
        </td>
    <? endforeach;?>
    </tr>
</table>
<? endif;?>
<hr>
<? if(isset($statsValue['pageFlow']['nofStats'])):?>
<table class="resultTable">
     <tr>
        <th style="text-align: left;" id="alysTblHeading"  colspan="2">
        <h2>Page Flows</h2>
        </th>
    </tr>
    <tr>
        <th>Number of Flows</th>
        <th>Test Cases List</th>
    </tr>
    <? foreach($statsValue['pageFlow']['nofStats'] as $pgStat):  ?>
    <tr>
        <td>
            <b><? print $pgStat['counter']; ?></b> Test Cases had
            <b><? print $pgStat['numOfFlow'];?></b> Page Flows.
        </td>
        <td>
            <ul>
            <? foreach($pgStat['testCases'] as $tc):?>
                <li><? print $tc;?></li>
            <? endforeach;?>
            </ul>
        </td>
    </tr>
    <? endforeach;?>
</table>
<? endif;?>
</div>

<h3>Error Stats</h3>
<div id="alysSegments">
<? if(isset($statsValue['failureCommonality'])):?>
<table class="resultTable">
    <tr>
        <th style="text-align: left;" id="alysTblHeading"  colspan="2">
        <h2>Failure Detail Commonality</h2>
        </th>
    </tr>
    <tr>
        <th>Failure Description</th>
        <th>Test Cases</th>
    </tr>
    <? foreach(reportsuiteanalysishelper::sortArrayOnCounterElement($statsValue['failureCommonality'], SORT_DESC) 
            as $failCommon):?>
    <tr>
        <td>
            <b>Error Message:</b><br /> 
                <? print str_replace("::", "<br /><br />", $failCommon['errMsg']); ?>
            <p>(<? print $failCommon['counter']?> Occurrences)</p>
        </td>
        <td>
            <ul>
            <? foreach($failCommon['testCases'] as $tc):?>
                <li><? print $tc;?></li>
            <? endforeach;?>
            </ul>
        </td>
    </tr>
    <? endforeach;?>
</table>
<? endif;?>
<hr>

<? if(isset($statsValue['pointOfFailureAS'])):?>
<table class="resultTable">
     <tr>
        <th style="text-align: left;" id="alysTblHeading"  colspan="2">
        <h2>Point of Failure Action Steps</h2>
        </th>
    </tr>
    <tr>
        <th>Point of Failure (ActionSteps)</th><th>Test Cases</th>
    </tr>
    <? foreach(reportsuiteanalysishelper::sortArrayOnCounterElement($statsValue['pointOfFailureAS'], SORT_DESC) 
            as $pofAS):?>
    <tr>
        <td>
            <? $vi=1;
            foreach($pofAS['actionStepDetail'] as $asK=>$asV):
                switch($asK):
                case 0:?>
            <b>Keyword: </b><?print $asV?><br />
                    <?break;
                case 1:?>
            <b>Fail Priority: </b><?print $asV?><br />
                    <?break;
                default:?>
            <?if(!empty($asV)):?>
            <b>Variable <?print $vi;?>: </b><?print $asV;$vi++;?><br />
            <?  endif;?>
                    <?break;
                endswitch;
            endforeach;?>
            <p>(<? print $pofAS['counter']?> Occurrences)</p>
            
        </td>
        <td>
              <ul>
            <? foreach($pofAS['testCases'] as $tc):?>
                <li><? print $tc;?></li>
            <? endforeach;?>
            </ul>
        </td>
    </tr>
    <? endforeach; ?>
</table>
<? endif;?>
</div>
</div>
</fieldset>

<? endforeach; ?>
