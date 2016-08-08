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
class reportsuiteweeklyreporting 
{
    
    protected static $projectAnalysisPage = 
            "http://10.153.30.100/?q=reportsuite/automationAnalysis/%tpid%/timespan/weekly";
    
    public static function overallWeeklyReportData($customWeek = array(), 
            $representInTable = true)
    {
        $overallWeeklyReportPackage = array();
        
        if(!empty($customWeek))
        {
            $weekFrom = $customWeek['weekFrom'];
            $weekTo = $customWeek['weekTo'];
        }
        else
        {
            $weekFrom = date('Y-m-d H:i:s', strtotime("-1 week"));
            $weekTo = date('Y-m-d H:i:s');
        }
        
        //drupal_set_message($weekFrom.' - '.$weekTo);
        
        $compiledData = array();
        
        //get all tpid
        foreach (reportsuitequeries::getAllTpid() as $tpid => $projectInfo)
        {
            $tcrData = reportsuitehlanalysis::getAllTestCaseResultsDataByTimeSpan(
            $tpid, 'PST', array('from' => $weekFrom, 'to' => $weekTo));
            
            $prH = null;
            $prA = null;
            $prL = null;
            
            $compiledData[$tpid]['projectInfo'] = $projectInfo;
            $compiledData[$tpid]['data']['totalCaseRan'] = 0;
            $compiledData[$tpid]['data']['totalPassed'] = 0;
            $compiledData[$tpid]['data']['runBy'] = array();
            
            if(!empty($tcrData))
            {
                foreach ($tcrData as $reid => $data)
                {
                  $compiledData[$tpid]['customer'] = $projectInfo['CustomerAlias'];
                  
                  if(isset($compiledData[$tpid]['data']['timesRan']))
                      $compiledData[$tpid]['data']['timesRan']++;
                  else
                      $compiledData[$tpid]['data']['timesRan'] = 1;
                  
                  $rePassRate = reportsuitequeries::calculatePassRate(
                                $data['CombineTotal']['Total'], 
                                $data['CombineTotal']['Total Passed']);
                  
                  //determine passrate
                  //high
                  if($prH !== null)
                      $prH = (float)$rePassRate < (float)$prH ? $prH : $rePassRate;
                    else
                      $prH = $rePassRate;
                  //Low 
                  if($prL !== null)
                      $prL = (float)$rePassRate > (float)$prL ? $prL : $rePassRate;
                  else 
                      $prL = $rePassRate;
                  
                  if(!in_array($data['additionalDets']['runBy'], 
                     $compiledData[$tpid]['data']['runBy']))
                  $compiledData[$tpid]['data']['runBy'][] = 
                    $data['additionalDets']['runBy'];
                  
                  $compiledData[$tpid]['data']['totalCaseRan'] += 
                    $data['CombineTotal']['Total'];
                  
                  $compiledData[$tpid]['data']['totalPassed'] +=
                    $data['CombineTotal']['Total Passed'];
                  
                }
                
                $compiledData[$tpid]['data']['passRate']['highest'] = 
                    reportsuite::addVisiualEffectOnPR($prH);
                $compiledData[$tpid]['data']['passRate']['average'] = 
                    reportsuitequeries::calculatePassRate(
                     $compiledData[$tpid]['data']['totalCaseRan'], 
                     $compiledData[$tpid]['data']['totalPassed'],
                     true);
                $compiledData[$tpid]['data']['passRate']['lowest'] = 
                    reportsuite::addVisiualEffectOnPR($prL);
                
            }
            else
            {
                $compiledData[$tpid] = 
                    array('customer' => $projectInfo['CustomerAlias'],
                          'projectInfo' => $projectInfo,
                          'data' => 'No Data');
            }
        }
        
        if($representInTable)
            return self::putOverallWeeklyReportDataIntoTable($compiledData);
        else
            return $compiledData;
    }
    
    protected static function putOverallWeeklyReportDataIntoTable($wrData)
    {
        $table = '<table border="1" style="border-spacing: 0;">';
        $table .= '<tr style="background-color:#E0E0E0;">';
        $table .= '<th>Project</th>';
        $table .= '<th>Total # of Run</th>';
        $table .= '<th>Highest PR</th>';
        $table .= '<th>Average PR</th>';
        $table .= '<th>Lowest PR</th>';
        $table .= '<th>Total Cases Ran</th>';
        $table .= '<th>Total Cases Pass</th>';
        $table .= '<th>Run by</th>';
        $table .= '</tr>';
        
        foreach ($wrData as $tpid => $wrData)
        {
          $table .= '<tr >';
          
          $table .= '<td>';
          $table .= '<a href="http://pstautomation.datalex.com'.
                    '/?q=reportsuite/summary/'.$tpid.'/'.
                    $wrData['projectInfo']['Customer'].'-'.$wrData['projectInfo']['POS'].
                    '-historicalRun/PST">'.
                    $wrData['customer'].
                    '</a>';
          $table .= '</td>';
          
          if($wrData['data'] !== 'No Data')
          {
            $table .= '<td>'.$wrData['data']['timesRan'].'</td>';
            $table .= '<td>'.$wrData['data']['passRate']['highest'].'</td>';
            $table .= '<td>'.$wrData['data']['passRate']['average'].'</td>';
            $table .= '<td>'.$wrData['data']['passRate']['lowest'].'</td>';
            $table .= '<td>'.$wrData['data']['totalCaseRan'].'</td>';
            $table .= '<td>'.$wrData['data']['totalPassed'].'</td>';
            $table .= '<td>';
            foreach($wrData['data']['runBy'] as $runBy)
                $table .= $runBy.'<br />';
            $table .= '</td>';
          }
          else
          {
              $table .= '<td colspan="7">
                         <center>Automation did <b>not</b> run this week</center>
                         </td>';
          }
          $table .= '</tr>';
        }
        
        $table .= '</table>';
        
        return $table;
    }
    
    
    
    public static function sendWeeklyReport($data)
    {
     
        $htmlContent = '<h1>Weekly Report</h1>';
        
        $htmlContent .= '<p>'.
                        'Week: '.$data['week']['from'].
                        ' - '.$data['week']['to'].
                        '</p>';
        
        $htmlContent .= $data['table']['weeklyReport']['overall'];
        
        $htmlContent .= '<p>Report generated by PST Automation Bot. 
                        '.reportsuite::$copyrightText.'</p>';
        
        
        if(self::postmanSend('default_from', 
                 implode(',', reportsuiteXmlProcessor::getMailingList('WeeklyReporting', 'overall')), 
                 'PST Automation Weekly Report', $htmlContent))
            drupal_set_message('Email Sent');
        else
            drupal_set_message('Something when terrible wrong');
    }
    
    private static function postmanSend($from = 'default_from', $to, $subject, $bodyContent)
    {
        $module = 'reportsuite';
        $key = microtime();
        if ($from == 'default_from') {
          // Change this to your own default 'from' email address.
          $from = 'noreply@pstautomation.datalex.com';
        }

        $message = drupal_mail($module, $key, 
                   $to,language_default(), array(), $from, false);
        $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed';
        $message['body'] = $bodyContent;
        $message['subject'] = $subject;
        
        $system = drupal_mail_system($module, $key);
        //$message = $system->format($message);
        if ($system->mail($message)) {
          return TRUE;
        }
        else {
            drupal_set_message('<pre>' . print_r($res, 1) . '</pre>');
          return FALSE;
        }
    }
    
    private static function sendWeeklyReportWithAttachmentOnProject($to, $cc, $customer, $link)
    {
        
//        $base64 = base64_encode($file_content);
        $bodyContent = '
Hi,<br /><br />

Attached is your automation testing weekly report.<br /><br />

For more detailed information, please follow/click on the link; 
<b><a href="'.$link.'">Full Report</a></b><br /><br />



Regards,<br />
PST Automation<br /><br />
<span style="font-size;10px;">
Note: This is an auto-generated mail, please do note reply this mail. <br />
Any automation support request should be sent to automation-support@datalex.com and <br />
general enquiries to pst-automation@datalex.com.​         
</span><br /><br />
<p>Report generated by PST Automation Bot. '.reportsuite::$copyrightText.'</p>
';
        $module = 'reportsuite';
        $key = microtime();
        $from = 'noreply@pstautomation.datalex.com';
        $filepath = 'sites/all/modules/00007167/'.
                'WebsystemDrupalModules/reportsuite/lib/weeklyReports/autoBot/'.str_replace(' ', '', $customer).'_weeklyReport.jpg';
        $file_content = file_get_contents($filepath); 
        $attachments = array(
           'filecontent' => $file_content,
           'filename' => str_replace(' ', '', $customer).'_weeklyReport.jpg',
           'filemime' => 'image/jpeg',
         );
        
        
//        $bodyContent .= "Content-Type: image/jpg; name=\"".str_replace(' ', '', $customer)."_weeklyReport.jpg"."\"\r\n"
// 	."Content-Transfer-Encoding: base64\r\n"
// 	."Content-disposition: attachment; file=\"".str_replace(' ', '', $customer)."_weeklyReport.jpg"."\"\r\n"
// 	."\r\n"
// 	.chunk_split(base64_encode($file_content));
        
        $bodyContent .= '<img src="cid:'.str_replace(' ', '', $customer).'_weeklyReport.jpg'.'" />';
        
            $message = drupal_mail($module, $key, 
                       $to,language_default(), array(), $from, false);
            
            $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed';
            $message['headers']['Cc'] = $cc;
            $message['body'] = $bodyContent;
            $message['subject'] = $customer.' Automated Testing Weekly Report';
            $message['params']['attachments'][] = $attachments;
            $system = drupal_mail_system('mime', 'notice');
            $message = $system->format($message);
            
              if ($system->mail($message)) {
              return TRUE;
            }
            else {
              return FALSE;
            }
    }
    
    public static function processAndPostProjectWeeklyReports()
    {
        $projectList = reportsuite::customerAlias(0, true);
        $testLeadMailingList = reportsuiteXmlProcessor::getMailingList('ProjectLeads', 'PST');
        
        $weekFrom = strtotime("-1 week");
        $weekTo = strtotime('now');
        
         /////////////////////////////////
         ////PRODUCTION/////
        $to = 'noLeadAssigned@datalex.com';
        $cc = 'PST-Automation@datalex.com, PSTArchitectGroup@datalex.com, PSTManagement@datalex.com';
        
        drupal_set_message(date('Y-m-d',$weekFrom).'-'.date('Y-m-d',$weekTo).'--'.date('Y-m-d'));
        $customWeekStringAddon = '--pchangeCustomTime--tsf'.$weekFrom.'--tst'.$weekTo;
        ////////////////////
        ////Test////
//        $to = 'jossie.saul@datalex.com';
////        $cc = 'jossie.saul@datalex.com';
//        $cc = 'PST-Automation@datalex.com';
//        self::takeScreenshotOfAnalyticsPage(1, $customWeekStringAddon);
//         self::sendWeeklyReportWithAttachmentOnProject(
//            $to,
//            $cc,
//            'Malaysia Airlines', 
//            'http://pstautomation.datalex.com/?q=reportsuite/automationAnalysis/1/timespan/weekly'.$customWeekStringAddon);
//         
        drupal_set_message('http://pstautomation.datalex.com/?q=reportsuite/automationAnalysis/1/timespan/weekly'.$customWeekStringAddon);
         
            //drupal_set_message();
            foreach($projectList as $tpid => $project)
            {
                if(!empty($project))
                {
                    if(isset($project['disabled']) && $project['disabled'] == true){}
                    else{
                        $customer = reportsuite::customerAlias($tpid);
                        
                        //replace/override to project owner
                        foreach($testLeadMailingList as $regEntry)
                            if($regEntry['projectid'] == $tpid)
                                $to=$regEntry['email'];
//                        
                        self::takeScreenshotOfAnalyticsPage($tpid,  $customWeekStringAddon);
                        self::sendWeeklyReportWithAttachmentOnProject(
                                $to,
                                $cc,
                                $customer['name'], 
                                'http://pstautomation.datalex.com/?q=reportsuite/automationAnalysis/'.$tpid.'/timespan/weekly'.$customWeekStringAddon
                                );
                    }
                }
            }
        
        return;
        
    }
    
    public static function takeScreenshotOfAnalyticsPage($tpid, $urlAddon = '', $activeUser = false)
    {
        global $user;
        $customer = reportsuite::customerAlias($tpid);
        $orgPath = getcwd();
        $wkhtmlPath = $orgPath.
                '/sites/all/modules/00007167/WebsystemDrupalModules/reportsuite/lib/wkhtmltox/bin';
        //drupal_set_message($oldPath);
        chdir($orgPath.
                '/sites/all/modules/00007167/WebsystemDrupalModules/reportsuite/lib/weeklyReports');
        
        if($activeUser){
            chdir('onRequest');
        }else{
            chdir('autoBot');
        }
        
        $filenameToProduce = str_replace(' ', '', $customer['name']).'_weeklyReport.jpg';
        exec($wkhtmlPath.'/wkhtmltoimage '.
                str_replace('%tpid%', $tpid, self::$projectAnalysisPage).$urlAddon.' '.$filenameToProduce,  $updateFm);
        chdir($orgPath);
        
         if ($user->uid == 1)
            drupal_set_message('<pre>' . print_r($updateFm, 1) . '</pre>');
        return;
    }
}

?>
