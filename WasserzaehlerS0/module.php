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
            $this->RegisterPropertyBoolean('Daily', false);
            $this->RegisterPropertyBoolean('PreviousDay', false);
            $this->RegisterPropertyBoolean('PreviousWeek', false);
            $this->RegisterPropertyBoolean('CurrentMonth', false);
            $this->RegisterPropertyBoolean('LastMonth', false);
            $this->RegisterPropertyBoolean('Price', false);
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
			
            $this->MaintainVariable('TodayConsumption', $this->Translate('Daily Consumption'), 2, '~Water', 4, $this->ReadPropertyBoolean('Daily') == true);

            $this->MaintainVariable('PreviousDayConsumption', $this->Translate('Previous Day Consumption'), 2, '~Water', 6, $this->ReadPropertyBoolean('PreviousDay') == true);

            $this->MaintainVariable('PreviousWeekConsumption', $this->Translate('Previous Week Consumption'), 2, '~Water', 8, $this->ReadPropertyBoolean('PreviousWeek') == true);

            $this->MaintainVariable('CurrentMonthConsumption', $this->Translate('Previous Month Consumption'), 2, '~Water', 10, $this->ReadPropertyBoolean('CurrentMonth') == true);

            $this->MaintainVariable('LastMonthConsumption', $this->Translate('Last Month Consumption'), 2, '~Water', 12, $this->ReadPropertyBoolean('LastMonth') == true);

            $this->MaintainVariable('DailyCost', $this->Translate('Daily Cost'), 3, '~Euro', 12, $this->ReadPropertyFloat('Price') == true);

            //$this->MaintainVariable('LastMonthConsumption', $this->Translate('Last Month Consumption'), 2, '~Water', 12, $this->ReadPropertyBoolean('LastMonth') == true);
			
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

            if ($this->ReadPropertyBoolean('Daily')) {
                $result = $this->calculate(strtotime('today 00:00'), time());
                $this->SetValue('TodayConsumption', $result['consumption']);
                $this->SetValue('')
               
            }
            if ($this->ReadPropertyBoolean('PreviousDay')) {
                $result = $this->calculate(strtotime('yesterday 00:00'), strtotime('yesterday 23:59'));
                $this->SetValue('PreviousDayConsumption', $result['consumption']);
               
            }

            if ($this->ReadPropertyBoolean('PreviousWeek')) {
                $result = $this->calculate(strtotime('last Monday'), strtotime('next Sunday 23:59:59'));
                $this->SetValue('PreviousWeekConsumption', $result['consumption']);
                
            }

            if ($this->ReadPropertyBoolean('CurrentMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month'), strtotime('last day of this month 23:59:59'));
                $this->SetValue('CurrentMonthConsumption', $result['consumption']);
                
            }

            if ($this->ReadPropertyBoolean('CurrentMonth')) {
                $result = $this->calculate(strtotime('midnight first day of this month'), strtotime('last day of this month 23:59:59'));
                $this->SetValue('CurrentMonthConsumption', $result['consumption']);
                
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
           
                $hour = null;
                
                $values = AC_GetAggregatedValues($archiveID, $consumptionVariableID, 0, $startDate, $endDate, 0);

            

                    foreach ($values as $key => $value) {
                        $tmpValueAVG = $value['Avg'];
                        $tmpValueAVG = $value['Avg'] / $this->ReadPropertyInteger('Impulse_l');
                        $consumption += $tmpValueAVG;
                    }
          
            return ['consumption' => round($consumption, 2)];
          }
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
		}

		
	}

    // Wasser Kosten Trinkwasser 0,92 Euro/m³
    // Wasser Kosten Abwasser 2,20 Euro/m³