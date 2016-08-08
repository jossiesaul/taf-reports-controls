<?php

?>
<h2><?php print $title; ?></h2>

        <div id="accordion">
        <h3><a href="#">Project Automation Overview</a></h3>
          <div id="accordion-normal">
              
              <?php
              
                reportsuiteweeklyreporting::overallWeeklyReportData();
              
              ?>
              
            <p>
            Total Unique Test Cases <br />
            Total Test Cases being Run (grabbed from the last run) <br />

            Statistic<br />
            Total Run To Date<br />
            Total Passes To Date <br />

            Graph Chart Maybe <br />
            </p>
          </div>

           <?php

                foreach($listOfProjects as $tpid => $projectInfo)
                {
                    print '<h3><a href="#">'.$projectInfo['CustomerAlias'].'</a></h3>';
                    print '<div id="accordion-resizer">';
                    print $dataByTpid[$tpid]['table'];
                    print '<br />';
                    print $listOfCoverage[$tpid]['coverage'];
                    print '<div id="tabs-tpid'.$tpid.'">';
                    print '<ul>';
                    print '<li><a href="#tabs-1">Automated Cases Growth</a></li>';
                    print '<li><a href="#tabs-2">Pass to Fail Ratio Bar</a></li>';
                    print '<li><a href="#tabs-3">RP Run Tracker</a></li>';
                    print '<li><a href="#tabs-4">RP Case Growth Tracker</a></li>';
                    print '</ul>';
                    print '<div id="tabs-1">';
                    print '<center>'.$dataByTpid[$tpid]['chart']['acg'].'</center>';
                    print '</div>';
                    print '<div id="tabs-2">';
                    print '<center>'.$dataByTpid[$tpid]['chart']['pfr'].'</center>';
                    print '</div>';
                    print '<div id="tabs-3">';
                    print $dataByTpid[$tpid]['chart']['rptr'];
                    print '</div>';
                    print '<div id="tabs-4">';
                    print $dataByTpid[$tpid]['chart']['rptcg'];
                    print '</div>';
                    print '</div>';
                    
                    print '</div>';
                }

           ?>

        </div>
    </div>
   
<?php
foreach ($listOfProjects as $tpid => $projectInfo)
{
    print '<div id="dialog-tpid'.$tpid.'" title="List Of Automated Test Cases">';
    if(isset($listOfTestCases[$tpid]))
    {
        foreach ($listOfTestCases[$tpid] as $scenario => $testCases)
        {
            print '<table>';
            print '<tr>';
            print '<th >'.$scenario.'</th>';
            print '<th># Of ActionSteps</th>';
            print '</tr>';
            foreach ($testCases as $tcid => $testCaseInfo)
            {
                print '<tr>';
                print '<td>';
                print '<span style="font-weight:bold;">'.$testCaseInfo['testcase_id']. '</span><br />';
                print '<span style="font-size:10px;">'.$testCaseInfo['description'].'</span>';
                print '</td>';
                print '<td>'.$testCaseInfo['num_of_actionsteps'].'</td>';
                print '</tr>';
            }
            print '</table>';
        }
    }
    
    print '</div>';
}

foreach ($listOfProjects as $tpid => $projectInfo)
{
    print '<div id="lastRunDialog-tpid'.$tpid.'" title="Last Run Result">';
    print '<div>test</div>';
    print '</div>';
}
?>
