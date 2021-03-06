<?php


/**
 * Description of reportsuite
 *
 * @author jossie.saul
 */
class reportsuitereporting {
    
    
    public static function sendReportToMailingsAfterAutoRun($projectName, $link){
        
         $bodyContent = '
Hi,<br /><br />

The Automation Test you have kicked off has been completed. Results Attached as a PDF report.<br /><br />

For more information, please follow/click on the link; 
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
                'WebsystemDrupalModules/reportsuite/lib/weeklyReports/onRequest/'.
                str_replace(' ', '', $projectName).'_resultNotification.pdf';
        $file_content = file_get_contents($filepath); 
        $attachments = array(
           'filecontent' => $file_content,
           'filename' => str_replace(' ', '', $projectName).'_resultNotification.pdf',
           'filemime' => 'application/pdf',
         );
        
        $string = file_get_contents(drupal_get_path('module', 'projectcontrol')
                .'/lib/notification/'.$projectName.'/NotifyAfterCompletion.json');
        
        $mailingList = json_decode($string, true);
        //$bodyContent .= '<img src="cid:'.str_replace(' ', '', $projectName).'_resultNotification.jpg'.'" />';
        drupal_set_message('<pre>' . print_r($mailingList, 1) . '</pre>');
        $message = drupal_mail($module, $key, 
                       $mailingList['Primary'],language_default(), array(), $from, false);
            
        $cc = implode(',', $mailingList['Secondary']);
        
        
        $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed';
        $message['headers']['Cc'] = $cc;
        $message['body'] = $bodyContent;
        $message['subject'] = $projectName.' Automated Testing Result Notification';
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
    
    
    public static function takeScreenShotOfPageOnURLToPdf($url, $filePrefix)
    {
        global $user;
        $customer = reportsuite::customerAlias($tpid);
        $orgPath = getcwd();
        $wkhtmlPath = $orgPath.
                '/sites/all/modules/00007167/WebsystemDrupalModules/reportsuite/lib/wkhtmltox/bin';
        //drupal_set_message($oldPath);
        chdir($orgPath.
                '/sites/all/modules/00007167/WebsystemDrupalModules/reportsuite/lib/weeklyReports/onRequest');
        
        drupal_set_message($url);
        $filenameToProduce = $filePrefix.'_resultNotification.pdf';
        exec($wkhtmlPath.'/wkhtmltopdf '.
                $url.' '.$filenameToProduce,  $updateFm);
        chdir($orgPath);
        
         if ($user->uid == 1)
            drupal_set_message('<pre>' . print_r($updateFm, 1) . '</pre>');
        return;
    }
    
    public static function isNotificationOfResultsIsRequested($customer)
    {
        return file_exists(drupal_get_path('module', 'projectcontrol').
                '/lib/notification/'.$customer.'/NotifyAfterCompletion.json');
    }
    
}
