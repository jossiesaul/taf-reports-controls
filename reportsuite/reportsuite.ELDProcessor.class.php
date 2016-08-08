<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reportsuite
 *
 * @author jossie
 * 
 * class for TestCase Execution List Data processor
 * 
 *  Strictly on per test case
 * 
 */
class reportsuiteELDProcessor {
    
    ///
    public static function getNumberOfTotalSteps($tcExecListData)
    {
        return count($tcExecListData);
    }
    
    public static function getNumberOfPassingSteps($tcExecListData, $tcData)
    {
        $failingActionSteps = reportsuite::processFailingActionStepsByTestCase($tcData);
        $warningActionSteps = self::getNumberWarningSteps($tcData);
        $caseFailed = $tcData['outcome'] == 'Failed'?true:false;
        $fi = 0;
        $tNumOfSteps = count($tcExecListData);
        if($caseFailed)
            $fi = $tNumOfSteps - (key($failingActionSteps)-1);  
        
        return $tNumOfSteps - ($fi + $warningActionSteps);
    }
    
    public static function getNumberFailingSteps($tcExecListData, $tcData)
    {
        $failingActionSteps = reportsuite::processFailingActionStepsByTestCase($tcData);
        $caseFailed = $tcData['outcome'] == 'Failed'?true:false;
        $fi = 0;
        $tNumOfSteps = count($tcExecListData);
        if($caseFailed)
            $fi = $tNumOfSteps - (key($failingActionSteps)-1);  
        
        return $fi;
    }
    
    public static function getNumberWarningSteps($tcData)
    {
        $failingActionSteps =  reportsuite::processFailingActionStepsByTestCase($tcData);
        $i=0;
        if(count($failingActionSteps) > 1)
        foreach ($failingActionSteps as $wi)
        {
            if($wi == 'Warning') $i++;
        }
        return $i;
    }
    
    public static function getfailingPointStep($execListData, $testCaseData)
    {
        $failingActionStep = reportsuite::processFailingActionStepsByTestCase($testCaseData);
       // drupal_set_message('<pre>' . print_r($failingActionStep, 1) . '</pre>');
        $fasArr = array();
        if(empty($failingActionStep)) return array();
        
        foreach($failingActionStep as $fasKey => $fasValue)
        {
            //actual arr numbering
            $i = $fasKey-1;
            if(isset($execListData[$i]))
            {
                $fasArr[$i] = array(
                    'status' => $fasValue,
                    'actionStepNum' => $i,
                    'actionStepDetail' => $execListData[$i]
                    );
            }
        }
        return $fasArr;
    }
    
    public static function getRoutes($tcExecListData)
    {
        $routes = array();
        foreach($tcExecListData as $dt)
        {
            if($dt[0] == 'Selenium.typeFlight' || $dt[0] == 'Selenium.selectOptionByValue' ||
                $dt[0] == 'Selenium.typeToInput')
            {
                switch($dt[2])
                {
                    case 'inputFrom':
                        $routes['origin'] = $dt[3];
                        break;
                    case 'inputTo':
                        $routes['destination'] = $dt[3];
                        break;
                    case 'inputFromMC1':
                        $routes['origin_MultiCity1'] = $dt[3];
                        break;
                    case 'inputFromMC2':
                        $routes['origin_MultiCity2'] = $dt[3];
                        break;
                    case 'inputFromMC3':
                        $routes['origin_MultiCity3'] = $dt[3];
                        break;
                    case 'inputFromMC4':
                        $routes['origin_MultiCity4'] = $dt[3];
                        break;
                    case 'inputFromMC5':
                        $routes['origin_MultiCity5'] = $dt[3];
                        break;
                    case 'inputToMC1':
                        $routes['destination_MultiCity1'] = $dt[3];
                        break;
                    case 'inputToMC2':
                        $routes['destination_MultiCity2'] = $dt[3];
                        break;
                    case 'inputToMC3':
                        $routes['destination_MultiCity3'] = $dt[3];
                        break;
                    case 'inputToMC4':
                        $routes['destination_MultiCity4'] = $dt[3];
                        break;
                    case 'inputToMC5':
                        $routes['destination_MultiCity5'] = $dt[3];
                        break;
                     case 'selectFrom':
                        $routes['origin'] = $dt[3];
                        break;
                    case 'selectTo':
                        $routes['destination'] = $dt[3];
                        break;
                    case 'selectFromMC1':
                        $routes['origin_MultiCity1'] = $dt[3];
                        break;
                    case 'selectFromMC2':
                        $routes['origin_MultiCity2'] = $dt[3];
                        break;
                    case 'selectFromMC3':
                        $routes['origin_MultiCity3'] = $dt[3];
                        break;
                    case 'selectFromMC4':
                        $routes['origin_MultiCity4'] = $dt[3];
                        break;
                    case 'selectFromMC5':
                        $routes['origin_MultiCity5'] = $dt[3];
                        break;
                    case 'selectToMC1':
                        $routes['destination_MultiCity1'] = $dt[3];
                        break;
                    case 'selectToMC2':
                        $routes['destination_MultiCity2'] = $dt[3];
                        break;
                    case 'selectToMC3':
                        $routes['destination_MultiCity3'] = $dt[3];
                        break;
                    case 'selectToMC4':
                        $routes['destination_MultiCity4'] = $dt[3];
                        break;
                    case 'selectToMC5':
                        $routes['destination_MultiCity5'] = $dt[3];
                        break;
                }
            }
        }
        return $routes;
    }
    
    public static function getSearchType($tcExecListData)
    {
        $searchType = 'Return';
        foreach($tcExecListData as $dt)
        {
            if(isset($dt[2]))
            {
                    if($dt[2] == 'radioOneWay')
                      $searchType = 'OneWay';
                    
                    if($dt[2] == 'radioMultiCity')
                        $searchType = 'MultiCity';
            }
        }
        return $searchType;
    }
    
    public static function getPageFlows($tcExecListData, $tcData)
    {
        $flowCheckpoints = array();
        $failingActionSteps =  reportsuite::processFailingActionStepsByTestCase($tcData);
        $i = 1;
        $fci = 1;
        $caseFailed = false;
        
        $advanceSearchPage = array('AirSearchExternalForward.do', 'ApplicationStartAction.do',
               'AirLowFareSearchExternal.do');
        
        $frontPageMatch = false;
        $advanceSearchPatchMatch = false;
        
        foreach($tcExecListData as $dt)
        {
            if(!$frontPageMatch)
            {
                foreach($advanceSearchPage as $asp)
                {
                    if(isset($dt[2]) && preg_match('/'.$asp.'/', $dt[2]));
                        $frontPageMatch = true;
                        $advanceSearchPatchMatch = true;
                }
                
                if($frontPageMatch && $advanceSearchPatchMatch)
                    $flowCheckpoints[0] = array(
                                'checkpointId' => 'pageAdvancedSearch',
                                'status' => 'NA'
                            );
            }
            
            $caseWarning = false;
            if(isset($failingActionSteps))
            foreach ($failingActionSteps as $key => $value)
            {
                if($key === $i)
                {
                    if($value == 'Failed')
                    {
                        $caseFailed = true;
                    }
                    else if ($value == 'Warning')
                    {
                       $caseWarning = true;
                    }
                }
            }
            
            if($dt[0] == 'Selenium.waitPageIsLoaded')
            {
                if($caseFailed)
                {
                    $caseFailed = true;
                    $flowCheckpoints[$fci] = array(
                                'checkpointId' => $dt['2'],
                                'status' => 'Failed'
                            );
                }
                else if ($caseWarning)
                {
                    $flowCheckpoints[$fci] = array(
                                'checkpointId' => $dt['2'],
                                'status' => 'Warning'
                            );
                }
                else
                {
                    $caseSt = $caseFailed?'Failed':'Passed';
                    $flowCheckpoints[$fci] = array(
                                    'checkpointId' => $dt['2'],
                                    'status' => $caseSt
                                );
                }
                $fci++;
            }
            $i++;
        }
        return $flowCheckpoints;
    }
    
    public static function getPaymentType($tcExecListData)
    {
        $paymentType = null;
        foreach ($tcExecListData as $dt)
        {
            if($dt[0] == 'Selenium.fillPage' && preg_match('/Payments/', $dt[2]))
            {
                
                switch(strtolower($dt[2]))
                {
                    case 'payments':
                        $paymentType = 'VISA';
                        break;
                    case 'paymentsmaster':
                        $paymentType = 'MasterCard';
                        break;
                    case 'paymentmaster':
                        $paymentType = 'MasterCard';
                        break;
                    case 'paymentsmastercard':
                        $paymentType = 'MasterCard';
                        break;
                    case 'paymentsamex':
                        $paymentType = 'American Express';
                        break;
                    case 'paymentsdinerclub':
                        $paymentType = 'Diners Club';
                        break;
                    case 'paymentsdiner':
                        $paymentType = 'Diners Club';
                        break;
                    case 'paymentsbarclay':
                        $paymentType = 'Barclay Card';
                        break;
                    case 'paymentsgiftcard':
                        $paymentType = 'Giftcard';
                        break;
                    case 'paymentsbml':
                        $paymentType = 'Bill me Later';
                        break;
                    case 'paymentsuatp':
                        $paymentType = 'UATP';
                        break;
//                    case 'Payments':
//                        $paymentType = $dt[2];
//                        break;
//                    case 'Payments':
//                        $paymentType = $dt[2];
//                        break;
//                    case 'Payments':
//                        $paymentType = $dt[2];
//                        break;
                    default:
                        $paymentType = $dt[2];
                        break;
                }
                break;
            }
        }
        
        return $paymentType;
    }
    
    public static function getPaxSelectionData($tcExecListData)
    {
        $paxArr = array('inputADTQuantity', 'inputCNNQuantity','inputCHDQuantity', 'inputINFQuantity',
            'inputINSQuantity', 'inputUNNQuantity', 'selectADTQuantity', 'selectCNNQuantity', 'selectINFQuantity',
            'selectINSQuantity');
               
        $paxInputs = array();
        $paxInputs['ADT'] = array(
                           'paxType' => 'Adult',
                           'amt' => 1,
                        );
        foreach($tcExecListData as $dt)
        {
            if(isset($dt[2]) && in_array($dt[2], $paxArr))
            {
                switch($dt[2])
                {
                    case 'inputADTQuantity':
                        $paxInputs['ADT'] = array(
                           'paxType' => 'Adult',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'inputCNNQuantity':
                        $paxInputs['CHD'] = array(
                           'paxType' => 'Child',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'inputCHDQuantity':
                        $paxInputs['CHD'] = array(
                           'paxType' => 'Child',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'inputINFQuantity':
                        $paxInputs['INF'] = array(
                           'paxType' => 'Infant',
                           'amt' => $dt[3],
                        );
                        break;
                     case 'inputINSQuantity':
                         $paxInputs['INFS'] = array(
                           'paxType' => 'Infant on Seat',
                           'amt' => $dt[3],
                        );
                        break;
                      case 'inputUNNQuantity':
                          $paxInputs['UNN'] = array(
                           'paxType' => 'Unaccompanied Minor',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'selectADTQuantity':
                        $paxInputs['ADT'] = array(
                           'paxType' => 'Adult',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'selectCNNQuantity':
                        $paxInputs['CHD'] = array(
                           'paxType' => 'Child',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'selectINFQuantity':
                        $paxInputs['INF'] = array(
                           'paxType' => 'Infant',
                           'amt' => $dt[3],
                        );
                        break;
                    case 'selectINSQuantity':
                        $paxInputs['INFS'] = array(
                           'paxType' => 'Infant on Seat',
                           'amt' => $dt[3],
                        );
                        break;
                    default :
                        $paxInputs[$i] = array(
                           'paxType' => 'Undefined',
                           'amt' => $dt[3],
                        );
                        break;
                }
            }
        }
        return $paxInputs;
    }
    
    public static function getInputDates($tcExecListData, $runDate)
    {
        $flightDates = array();
        
        $rdRaw = strtotime($runDate);
        //drupal_set_message('<pre>' . print_r($tcExecListData, 1) . '</pre>');
        foreach ($tcExecListData as $dt)
        {
             if($dt[0] == 'Selenium.inputDateByCalendar')
            {
                switch($dt[2])
                {
                    case 'inputDepartOn':
                        $flightDates['departDate'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputReturnOn':
                        $flightDates['returnDate'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputDepartOnMC1':
                        $flightDates['departDate_MultiCity1'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputDepartOnMC2':
                        $flightDates['departDate_MultiCity2'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputDepartOnMC3':
                        $flightDates['departDate_MultiCity3'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputDepartOnMC4':
                        $flightDates['departDate_MultiCity4'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                    case 'inputDepartOnMC5':
                        $flightDates['departDate_MultiCity5'] = date('d/m/Y', strtotime("+ ".$dt[3]." Days", $rdRaw));
                        break;
                }
            }
        }
        return $flightDates;
    }
    
    public static function getAncillaries($execListData)
    {
        $ancArr = array();
        foreach($execListData as $dt)
        {
            if(preg_match('/Selenium.purchase/', $dt[0]))
            {
                switch($dt[0])
                {
                    case 'Selenium.purchaseCar':
                        $ancArr[] = 'Car';
                        break;
                    case 'Selenium.purchaseInsurance';
                        if(isset($dt[2]) && $dt[2] == 'Yes')
                            $ancArr[] = 'Insurance';
                        break;
                    case 'Selenium.purchaseHotel':
                        $ancArr[] = 'Hotel';
                        break;
                }
            }
        }
        return $ancArr;
    }
    
}

?>
