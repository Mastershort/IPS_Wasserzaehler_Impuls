<?php

declare(strict_types=1);
	class WasserzaehlerS0 extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger('pulseVariableID', 0);
            $this->RegisterPropertyBoolean('Active', false);
            $this->RegisterPropertyBoolean('Today', false);
            $this->RegisterPropertyBoolean('PreviousDay', false);
            $this->RegisterPropertyBoolean('CurrentWeek', false);
            $this->RegisterPropertyBoolean('PreviousWeek', false);
            $this->RegisterPropertyBoolean('CurrentMonth', false);
            $this->RegisterPropertyBoolean('PreviousMonth', false);
            $this->RegisterPropertyBoolean('CurrentYear', false);
            $this->RegisterPropertyBoolean('TodayCosts', false);
            $this->RegisterPropertyBoolean('PreviousDayCosts', false);
            $this->RegisterPropertyBoolean('CurrentWeekCosts', false);
            $this->RegisterPropertyBoolean('PreviousWeekCosts', false);
            $this->RegisterPropertyBoolean('MonthlyCosts', false);
            $this->RegisterPropertyBoolean('PreviousMonthlyCosts', false);
            $this->RegisterPropertyBoolean('YearCosts', false);
            $this->RegisterPropertyBoolean('ActiveTotalCounter', false);
            $this->RegisterPropertyFloat('CurrentTotalCounter',0.00);
            $this->RegisterPropertyFloat('CalculatedWeeklyPrice',0.00);
            $this->RegisterPropertyFloat('CalculatedMonthlyPrice',0.00);
            $this->RegisterPropertyFloat('CalculatedYearPrice',0.00);
            $this->RegisterPropertyFloat('DrinkingWaterCost',0.00);
            $this->RegisterPropertyFloat('SewageCost',0.00);
            $this->RegisterPropertyInteger('Impulse_l',4);
			$this->RegisterPropertyInteger('UpdateInterval', 600);
			$this->RegisterTimer('WZ_UpdateCalculation', 0, 'WZ_updateCalculation($_IPS[\'TARGET\']);');

            
		
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
            $this->MaintainVariable('TodayConsumption', $this->Translate('Today Consumption'), 2, '~Water', 4, $this->ReadPropertyBoolean('Today') == true);
            $this->MaintainVariable('PreviousDayConsumption', $this->Translate('Previous Day Consumption'), 2, '~Water', 6, $this->ReadPropertyBoolean('PreviousDay') == true);
            $this->MaintainVariable('CurrentWeekConsumption', $this->Translate('Current Week Consumption'), 2, '~Water', 8, $this->ReadPropertyBoolean('CurrentWeek') == true);
            $this->MaintainVariable('PreviousWeekConsumption', $this->Translate('Previous Week Consumption'), 2, '~Water', 8, $this->ReadPropertyBoolean('PreviousWeek') == true);
            $this->MaintainVariable('CurrentMonthConsumption', $this->Translate('Current Month Consumption'), 2, '~Water', 10, $this->ReadPropertyBoolean('CurrentMonth') == true);
            $this->MaintainVariable('PreviousMonthConsumption', $this->Translate('Previous Month Consumption'), 2, '~Water', 12, $this->ReadPropertyBoolean('PreviousMonth') == true);
            $this->MaintainVariable('CurrentYearConsumption', $this->Translate('Current Year Consumption'), 2, '~Water', 12, $this->ReadPropertyBoolean('CurrentYear') == true);
            $this->MaintainVariable('CalculatedTodayCosts', $this->Translate('Today Costs'), 2, '~Euro', 14, $this->ReadPropertyBoolean('TodayCosts') == true);
            $this->MaintainVariable('CalculatedPreviousTodayCosts', $this->Translate('Previous Day Costs'), 2, '~Euro', 14, $this->ReadPropertyBoolean('PreviousDayCosts') == true);
            $this->MaintainVariable('CalculatedWeekCosts', $this->Translate('Current Week Costs'), 2, '~Euro', 16, $this->ReadPropertyBoolean('CurrentWeekCosts') == true);
            $this->MaintainVariable('CalculatedPreviousWeekCosts', $this->Translate('Previous Week Costs'), 2, '~Euro', 16, $this->ReadPropertyBoolean('PreviousWeekCosts') == true);
            $this->MaintainVariable('CalculatedMonthlyCosts', $this->Translate('Current Montly Costs'), 2, '~Euro', 18, $this->ReadPropertyBoolean('MonthlyCosts') == true);
            $this->MaintainVariable('CalculatedPreviousMonthlyCosts', $this->Translate('Previous Montly Costs'), 2, '~Euro', 18, $this->ReadPropertyBoolean('PreviousMonthlyCosts') == true);
            $this->MaintainVariable('CalculatedYearCosts', $this->Translate('Current Year Costs'), 2, '~Euro', 20, $this->ReadPropertyBoolean('YearCosts') == true);
            $this->MaintainVariable('CalculatedTotalCounter', $this->Translate('Calculated Total Counter'), 2, '~Water', 20, $this->ReadPropertyBoolean('ActiveTotalCounter') == true);

            
			
			$variableIdents = [];

            $variablePosition = 50;

            if ($this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('WZ_UpdateCalculation', $this->ReadPropertyInteger('UpdateInterval') * 1000);
                $this->updateCalculation();
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('WZ_UpdateCalculation', 0);
                $this->SetStatus(104);
            }
			
			}

		
		public function updateCalculation()
        {
           
            $totalConsumption = 0;

            if ($this->ReadPropertyBoolean('Today')) {
                $result = $this->calculate(strtotime('today 00:00'), time());
                $this->SetValue('TodayConsumption', $result['consumption']);
            if ($this->ReadPropertyBoolean('TodayCosts')) {
                $this->SetValue('CalculatedTodayCosts', $result['costs']);
              } 
               
            }
            if ($this->ReadPropertyBoolean('PreviousDay')) {
                $result = $this->calculate(strtotime('yesterday 00:00'), strtotime('yesterday 23:59'));
                $this->SetValue('PreviousDayConsumption', $result['consumption']);
             if ($this->ReadPropertyBoolean('PreviousDayCosts')) {
                $this->SetValue('CalculatedPreviousTodayCosts', $result['costs']);
              } 
               
            }
            if ($this->ReadPropertyBoolean('CurrentWeek')) {
                $result = $this->calculate(strtotime('last monday'), strtotime(' next Sunday 23:59:59'));
                $this->SetValue('CurrentWeekConsumption', $result['consumption']);
             if ($this->ReadPropertyBoolean('CurrentWeekCosts')) {
                $this->SetValue('CalculatedWeekCosts', $result['costs']);
              } 
            }

            if ($this->ReadPropertyBoolean('PreviousWeek')) {
                $result = $this->calculate(strtotime('last Monday - 1 week '), strtotime('next Sunday 23:59:59 - 1 week'));
                $this->SetValue('PreviousWeekConsumption', $result['consumption']);
                if ($this->ReadPropertyBoolean('PreviousWeekCosts')) {
                    $this->SetValue('CalculatedPreviousWeekCosts', $result['costs']);
                    }
            }

            if ($this->ReadPropertyBoolean('CurrentMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month'), strtotime('last day of this month 23:59:59'));
                $this->SetValue('CurrentMonthConsumption', $result['consumption']);
            if ($this->ReadPropertyBoolean('MonthlyCosts')) {
                $this->SetValue('CalculatedMonthlyCosts', $result['costs']);
                }
            }

            
            if ($this->ReadPropertyBoolean('PreviousMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month - 1 month'), strtotime('last day of this month 23:59:59 -1 month'));
                $this->SetValue('PreviousMonthConsumption', $result['consumption']);
             if ($this->ReadPropertyBoolean('PreviousMonthlyCosts')) {
                $this->SetValue('CalculatedPreviousMonthlyCosts', $result['costs']);
                }   
            }
            if ($this->ReadPropertyBoolean('CurrentYear')) {
                $result = $this->calculate(strtotime('midnight first day of this year'), time());
                $this->SetValue('CurrentYearConsumption', $result['consumption']);
             if ($this->ReadPropertyBoolean('YearCosts')) {
                $this->SetValue('CalculatedYearCosts', $result['costs']);
                }   
            }
            if ($this->ReadPropertyBoolean('ActiveTotalCounter')) {
                $result = $this->calculateTotal(strtotime('midnight first day of this year'), time());
                $this->SetValue('CalculatedTotalCounter', $result['totalCounter']);
               
            }
        }
            

            public function calculate($startDate, $endDate)
            {
                $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
                $consumptionVariableID = $this->ReadPropertyInteger('pulseVariableID');
                $consumption = 0;
                $price = 0;
           
                $hour = null;
                
                $values = AC_GetAggregatedValues($archiveID, $consumptionVariableID, 0, $startDate, $endDate, 0);

            

                    foreach ($values as $key => $value) {
                       
                        $tmpValueAVG = $value['Avg'] / $this->ReadPropertyInteger('Impulse_l');
                        $consumption += $tmpValueAVG;
                        
                    }
                    $calculatedCosts = ($this->ReadPropertyFloat('DrinkingWaterCost') + $this->ReadPropertyFloat('SewageCost') )/ 1000;
                    $costs = $consumption * $calculatedCosts;
                    
          
            return ['consumption' => round($consumption, 2),'costs' => round($costs, 2)];
          }
          public function calculateTotal(){
                $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
                $consumptionVariableID = $this->ReadPropertyInteger('pulseVariableID');
                $totalCount = 0;

                $values = AC_GetAggregatedValues($archiveID, $consumptionVariableID, 4, 0, 0, 0);
                foreach ($values as $key => $value) {
                       
                        $totalCountValueAVG = $value['Avg'] / $this->ReadPropertyInteger('Impulse_l');
                        $totalCount += $totalCountValueAVG;
                        
                    }

                $totalCount = $totalCount + $this->ReadPropertyFloat('CurrentTotalCounter');
                return['totalCounter'=>round($totalCount,2)];
          }
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
		}

		
	}

    // Wasser Kosten Trinkwasser 0,92 Euro/m?
    // Wasser Kosten Abwasser 2,20 Euro/m?