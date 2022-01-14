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
            $this->RegisterPropertyBoolean('LastMonth', false);
            $this->RegisterPropertyBoolean('TodayPrice', false);
            $this->RegisterPropertyBoolean('PreviousDayCosts', false);
            $this->RegisterPropertyBoolean('WeekCosts', false);
            $this->RegisterPropertyBoolean('MontlyPrice', false);
            $this->RegisterPropertyBoolean('YearPrice', false);
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
			
            $this->MaintainVariable('TodayConsumption', $this->Translate('Todaysdf  Consumption'), 2, '~Water', 4, $this->ReadPropertyBoolean('Today') == true);

            $this->MaintainVariable('PreviousDayConsumption', $this->Translate('Previous Day Consumption'), 2, '~Water', 6, $this->ReadPropertyBoolean('PreviousDay') == true);

            $this->MaintainVariable('CurrentWeekConsumption', $this->Translate('Current Week Consumption'), 2, '~Water', 8, $this->ReadPropertyBoolean('CurrentWeek') == true);

            $this->MaintainVariable('PreviousWeekConsumption', $this->Translate('Previous Week Consumption'), 2, '~Water', 8, $this->ReadPropertyBoolean('PreviousWeek') == true);

            $this->MaintainVariable('CurrentMonthConsumption', $this->Translate('Previous Month Consumption'), 2, '~Water', 10, $this->ReadPropertyBoolean('CurrentMonth') == true);

            $this->MaintainVariable('LastMonthConsumption', $this->Translate('Last Month Consumption'), 2, '~Water', 12, $this->ReadPropertyBoolean('LastMonth') == true);

            $this->MaintainVariable('CalculatedTodayPrice', $this->Translate('Today Price'), 2, '~Euro', 14, $this->ReadPropertyBoolean('TodayPrice') == true);

            $this->MaintainVariable('CalculatedPreviousTodayCosts', $this->Translate('Previous Day Costs'), 2, '~Euro', 14, $this->ReadPropertyBoolean('PreviousDayCosts') == true);

            $this->MaintainVariable('CalculatedWeekCosts', $this->Translate('Week Costs'), 2, '~Euro', 16, $this->ReadPropertyBoolean('WeekCosts') == true);

            $this->MaintainVariable('CalculatedMontlyPrice', $this->Translate('Montly Price'), 2, '~Euro', 18, $this->ReadPropertyBoolean('MontlyPrice') == true);

            $this->MaintainVariable('CalculatedYearPrice', $this->Translate('Year Price'), 2, '~Euro', 20, $this->ReadPropertyBoolean('YearPrice') == true);

            
			
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
                $this->SetValue('CalculatedTodayPrice', $result['price']);
               
               
            }
            if ($this->ReadPropertyBoolean('PreviousDay')) {
                $result = $this->calculate(strtotime('yesterday 00:00'), strtotime('yesterday 23:59'));
                $this->SetValue('PreviousDayConsumption', $result['consumption']);
                $this->SetValue('CalculatedPreviousTodayCosts', $result['price']);
               
               
            }
            if ($this->ReadPropertyBoolean('CurrentWeek')) {
                $result = $this->calculate(strtotime('last Monday'), strtotime(' next Sunday 23:59:59'));
                $this->SetValue('CurrentWeekConsumption', $result['consumption']);
                $this->SetValue('CalculatedWeekCosts', $result['price']);
                
            }

            if ($this->ReadPropertyBoolean('PreviousWeek')) {
                $result = $this->calculate(strtotime('last Monday'), strtotime('next Sunday 23:59:59'));
                $this->SetValue('PreviousWeekConsumption', $result['consumption']);
                
            }

            if ($this->ReadPropertyBoolean('CurrentMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month'), strtotime('last day of this month 23:59:59'));
                $this->SetValue('CurrentMonthConsumption', $result['consumption']);
                $this->SetValue('CalculatedMontlyPrice', $result['price']);
                
            }

            
            if ($this->ReadPropertyBoolean('LastMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month - 1 month'), strtotime('last day of this month 23:59:59 -1 month'));
                $this->SetValue('LastMonthConsumption', $result['consumption']);
                
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
                        $tmpValueAVG = $value['Avg'];
                        $tmpValueAVG = $value['Avg'] / $this->ReadPropertyInteger('Impulse_l');
                        $consumption += $tmpValueAVG;
                        
                    }
                    $calculatedPrice = ($this->ReadPropertyFloat('DrinkingWaterCost') + $this->ReadPropertyFloat('SewageCost') )/ 1000;
                    $price =$consumption * $calculatedPrice;
          
            return ['consumption' => round($consumption, 2),'price' => round($price, 2)];
          }
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
		}

		
	}

    // Wasser Kosten Trinkwasser 0,92 Euro/m³
    // Wasser Kosten Abwasser 2,20 Euro/m³