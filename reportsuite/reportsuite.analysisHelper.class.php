<?php

/*
 * To change this template, choose Tools | Templates        
 * and open the template in the editor.
 */

class reportsuiteanalysishelper
{
    
    /**
     * 
     * @param type $data
     * @param type $sort ASC DESC
     * @return type
     */
    public static function sortArrayOnCounterElement($data, $sort)
    {
        foreach ($data as $key => $row) {
            $counter[$key]  = $row['counter'];
        }
        array_multisort($counter, $sort, $data);
        return $data;
    }
    
    public static function getDurationByPeriodic($periodic, $periodChange)
    {
        date_default_timezone_set('Europe/London');
        $timespan = array();
        $dquery = '';
        switch($periodic)
        {
            case 'daily':
                $dqueryFrom = "Today";
                $dqueryTo = "Tomorrow";
                break;
            case 'weekly':
                $dqueryFrom = "monday this week";
                $dqueryTo = "sunday this week";
                break;
            case 'monthly':
                $dqueryFrom = "first day of this month";
                $dqueryTo = "last day of this month";
                break;
        }
        
        //Defaults
        $timespan['from'] = date('Y-m-d', strtotime($dqueryFrom));
        $timespan['to'] = date('Y-m-d', strtotime($dqueryTo));
        drupal_set_message($inst);
        $customDates = false;
        if(!empty($periodChange)){
            if(isset($periodChange['instruction'])){
                $inst = $periodChange['instruction'];
                if($inst == 'PlusOne'){
                    $inst = '+1 ';
                }else if($inst == 'MinusOne'){
                    $inst = '-1 ';
                }else if($inst == 'CustomTime'){
                    $inst = 'custom';
                    $customDates = true;
                }else{
                    drupal_set_message ('Instruction failed validation','error');
                    unset($inst);
                }
            }else{
                 drupal_set_message ('No Instruction Has been set','error');
            }
            
            if(isset($inst)){
                if($customDates){
                    $timespan['from'] = date('Y-m-d', $periodChange['from']);
                    $timespan['to'] = date('Y-m-d', $periodChange['to']);
                }else{
                    $timespan['from'] = date('Y-m-d', strtotime($inst.$periodChange['type'], $periodChange['from']));
                    $timespan['to'] = date('Y-m-d', strtotime($inst.$periodChange['type'], $periodChange['to']));
                }
            }
        }
        
        return $timespan;
    }
    
    public static function monthInformation(){
        return array('January' => array('from' => '1 January', 'to' => '31 January'),
                     'February' => array('from' => '1 February', 'to' => '28 February'),
                     'March' => array('from' => '1 March', 'to' => '31 March'),
                     'April' => array('from' => '1 April', 'to' => '30 April'),
                     'May' => array('from' => '1 May', 'to' => '31 May'),
                     'June' => array('from' => '1 June', 'to' => '30 June'),
                     'July' => array('from' => '1 July', 'to' => '31 July'),
                     'August' => array('from' => '1 August', 'to' => '31 August'),
                     'September' => array('from' => '1 September', 'to' => '30 September'),
                     'October' => array('from' => '1 October', 'to' => '31 October'),
                     'November' => array('from' => '1 November', 'to' => '30 November'),
                     'December' => array('from' => '1 December', 'to' => '31 December'),
            );
    }
    
    public static function getDurationByMonthYear($monthQueried,$yearQueried){
        
        $month = self::monthInformation();
        
        $timespan = array();
        $timespan['from'] = date('Y-m-d',strtotime($month[$monthQueried]['from'].' '.$yearQueried));
        $timespan['to'] = date('Y-m-d',strtotime($month[$monthQueried]['to'].' '.$yearQueried));
        return $timespan;
    }
    
    public static function searchResultsInMultiDimensionalArray($array, $key, $value)
    {
        $results = array();
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, self::searchKeyInMultiDimensionalArray($subarray, $key, $value));
            }
        }
        drupal_set_message('<pre>' . print_r($results, 1) . '</pre>');
        return $results;
    }
    
    public static function searchFailureCommonalityDuplicateErrorMsg($failureCommanilty, $needle)
    {
        foreach($failureCommanilty as $key => $dt)
        {
           if ($dt['errMsg'] === $needle )
              return $key;
        }
        return false;
    }
    
    public static function processTestRunResultsForGoogleChartData($testRunResults)
    {
        $automationResultStore = array();
        foreach($testRunResults as $reid => $dt){
            $automationResultStore[$reid] = array(
                'Total' => $dt['CombineTotal']['Total'], 
                'Pass' => $dt['CombineTotal']['Passed'],
                'Warning' => $dt['CombineTotal']['Warning'],
                'Fail' => $dt['CombineTotal']['Failed'],
                'TAFError' => $dt['CombineTotal']['TAFError']
             ); 
        }
        return $automationResultStore;
    }
    
}

?>
        