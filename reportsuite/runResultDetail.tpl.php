
<?php
print $data['link']['subLink']['runResult'];
?>
    
<table>
    <tr id="tblOverrideBg">
        <td style="text-align:center;" colspan="2">
            <?php print $data['link']['back']['summary'] ; ?>
        </td>
    </tr>
    <tr id="tblOverrideBg">
        <td style="text-align:left;">
          <?php print $data['link']['runExec']['prev']; ?>
        </td>
        <td style="text-align:right;">
          <?php print $data['link']['runExec']['next']; ?>
        </td>
    </tr>
</table>

<div id="testResContent">
<table>
    <tr>
    <th style="text-align:center;">In a Nutshell</th>
    <th style="text-align:center;">Outcome in a Pie</th>
    </tr>
    <tr style="background-color:#FFFFFF;">
        <td width=50% valign="top">
            <h2>Scenarios in Run:</h2>
            <ul>
                <?php 
                    foreach($data['result']['detail']['scenariosRan'] as $scenario)
                    {
                        print '<li>'.$scenario.'</li>';
                    }
                ?>
            </ul>
            <b>Run Date:</b> <?php print $data['result']['detail']['runDate']; ?>
            <br />
            <b>Run By:</b> <?php print $data['result']['detail']['runBy']; ?>
            <br />
            <b>Test Type:</b> <?php print $data['result']['detail']['testType']; ?>
            <br />
            <b>Test Environment:</b> <?php print $data['result']['detail']['testEnv']; ?>
            <br />
            <b>Release Phase:</b> <?php print $data['result']['detail']['releasePhase']; ?>
            <br />
            <b>Test Cycle:</b> <?php print $data['result']['detail']['testCycle']; ?>
            <br />
            <b>Release:</b> <?php print $data['result']['detail']['release']; ?>
            <br />
            <b>Browser:</b> <?php print $data['result']['detail']['browser']; ?>
            <br />
            <b>Total Execution Time:</b> <?php print $data['result']['detail']['totalExecutionTime']; ?>
            <br />
            <?php
            if($data['result']['detail']['testType'] === 'GRID')
                 print '&#9733; <i><b>Actual Total RunTime Duration (GRID):</b></i>  <u>'.
                    $data['result']['detail']['totalRunDurationTime'].'</u>'; 
            
            ?>
            <br />
            <b>Run Global Identifier:</b> <?php print $data['result']['detail']['rrUuid']; ?>
            
            
            <table>
                <tr>
                    <th style="text-align:center;">Pass Rate</th>
                    <th style="text-align:center;">TAF Error(s)</th>
                </tr>
                <tr>
                    <th id="rrSummShow"><?php print $data['result']['detail']['passRate']; ?></th>
                    <th id="rrSummShow"><?php print $data['result']['detail']['outcome']['error']; ?></th>
                </tr>
            </table>
            
        </td>    
        <td width=50% style="text-align:center;">
            
            <?php 
            if($data['result']['detail']['outcome']['totalTestCases'] === 
                    $data['result']['detail']['outcome']['failed'])
                print $data['path']['image']['fail'];
            else
                print reportsuite_resultOutcomePieChart($data['result']['detail']['outcome']);
            
            ?>
            
            <table>
                <tr>
                    <th colspan="3" style="text-align:center;">
                        Total Test Cases: 
                            <?php print $data['result']['detail']
                                        ['outcome']['totalTestCases'];?>
                    </th>
                </tr>
                
                <tr>
                    <th style="text-align: center;">Passed</th>
                    <th style="text-align: center;">Warning(s)</th>
                    <th style="text-align: center;">Failed</th>
                </tr>
                
                <tr>
                    <td id="resPassedTextValue">
                        <?php print $data['result']['detail']
                                        ['outcome']['passed'];?>
                    </td>
                    <td id="resWarningTextValue">
                        <?php print $data['result']['detail']
                                        ['outcome']['warning'];?>
                    </td>
                    <td id="resFailedTextValue">
                        <?php print $data['result']['detail']
                                        ['outcome']['failed'];?>
                    </td>
                </tr>
                
            </table>
        </td>
    </tr>
</table>

<table>
    <tr id="tblOverrideBg">
        <td>
            <div>
                <span id="underlineAndBold">Legend and Flag Information</span>
            </div>
            <div>
                <img src="sites/default/files/red-flag.png" width="25" height="25"/> 
                &nbsp;Test Case Status overwritten &nbsp;&nbsp;
                <img src="<?print drupal_get_path('module', 'reportsuite').
                 '/lib/rerun.png' ?>" width="25" height="25"/>&nbsp; Test Case Re-runned
                <br /><br />
                <?php print $data['path']['image']['failureReason']['legend']; ?>&nbsp;
                Test Case Failure Reason set 
            </div>
        </td>
        <td style="text-align:right;">
            <?php
            if(user_is_logged_in())
             {
               print '<button id="reRunFailuresFlagButton" style="padding: 0px;">';
               print $data['path']['image']['reRun']; 
               print '<br />';
               print 'Re-Run <br />Failure(s)';
               print '</button>';
               print '&nbsp;';
               print '<button id="badRunFlagButton" style="padding: 0px;">';
               print $data['path']['image']['badRun']; 
               print '<br />';
               print 'Bad Run';
               print '</button>';
               print '&nbsp;';
               print '<button id="runResultFailFlagButton" style="padding: 0px;">';
               print $data['path']['image']['failureReason']['button'];
               print '<br />';
               print 'Failure<br />Reason';
               print '</button>';
               print '&nbsp;';
               print '<button id="supportTicketButton" style="padding: 0px;">';
               print '<a href="mailto:automation-support@datalex.com?subject='
                     .$data['result']['detail']['title']
                     .'&body=[Please provide details of your support request and Link to page]">';
               print $data['path']['image']['supTicket'];
               print '</a>';
               print '<br />';
               print 'Request <br /> Support';
               print '</button>';               
             }
            ?>
        </td>
    </tr>
</table>
<br />
<?php 
foreach ($data['result']['report'] as $scenario => $rptData)
{
    print '<fieldset class="collapsible uncollapsed">';
    print '<legend><span class="fieldset-legend">';
    print '<a id="'.$scenario.'">'.$scenario.'</a>';
    print '</span></legend>';
    print '<div class="fieldset-wrapper">';
    
    print '<b><h3>Scenario Result Summary</h3></b>';
    print '<table>';
    print '<tr>';
    print '<th>PassRate</th>';
    print '<th>Total Test Cases</th>';
    print '<th>Passed</th>';
    print '<th>Warning</th>';
    print '<th>Failed</th>';
    print '</tr>';
    
    print '<tr>';
    print '<td>'.$rptData['summary']['PassRate'].'</td>';
    print '<td>'.$rptData['summary']['TotalTestCases'].'</td>';
    print '<td id="resPassedTextValue">'.$rptData['summary']['Passed'].'</td>';
    print '<td id="resWarningTextValue">'.$rptData['summary']['Warning'].'</td>';
    print '<td id="resFailedTextValue">'.$rptData['summary']['Failed'].'</td>';
    print '</tr>';
    print '</table>';
    
    print '<b><h3>Scenario Result Details</h3></b>';
    print $rptData['table'];
    print '</div>';
    print '</fieldset>';
}

?>

<div id="runExecFailureReasonDialog" title="Run Execution -  Failure Reason">
    <p class="validateTips">All Form Fields are required</p>
    <?php
       //
        print drupal_render(drupal_get_form('reportsuite_resultFailureReason_form', 
                $data['result']['detail']['reid']));
    ?>
    
</div>

<div id="badRunDialog" title="Run Execution - Bad Run">
    <?php
        print drupal_render(drupal_get_form('reportsuite_runExecutionFlag_form', 
                $data['result']['detail']['reid']));
    ?>
    
</div>

<div id="reRunFailuresDialog" title="Re-run Failures">
    <?php
        print drupal_render(drupal_get_form('reportsuite_reRun_form' ,$data['result']['detail']['reid']));
    ?>
</div>

<?php 

foreach ($data['result']['report'] as $scenario => $rptData)
     {
         foreach($rptData['detail'] as $testCaseId => $tcrDt)
         {
           print '<div id="fr_'.$testCaseId.'_Dialog" 
                  title="Test Cases '.$testCaseId.' - Failure Reason">';
           print drupal_render(drupal_get_form('reportsuite_testcaseFailureReason_form', 
                $data['result']['detail']['reid'], $scenario, $testCaseId));
           print '</div>';
           
//           print '<div id="oo_'.$testCaseId.'_Dialog" 
//                  title="Test Cases '.$testCaseId.' - Override Outcome">';
//           print drupal_render(drupal_get_form('reportsuite_overrideOutcomeForm', 
//                $data['result']['detail']['reid'], $testCaseId));
//           print '</div>';
           
         }
     }

?>

</div>