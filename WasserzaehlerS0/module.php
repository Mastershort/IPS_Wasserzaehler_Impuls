<?php

declare(strict_types=1);
	class WasserzaehlerS0 extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent('{D2040875-0467-89CE-8572-65F3F0D29F18}');
			$this->RegisterPropertyInteger('pulseVariableID', 0);

			$this->RegisterPropertyBoolean('Active', false);
            $this->RegisterPropertyBoolean('Daily', false);
            $this->RegisterPropertyBoolean('PreviousDay', false);
            $this->RegisterPropertyBoolean('PreviousWeek', false);
            $this->RegisterPropertyBoolean('CurrentMonth', false);
            $this->RegisterPropertyBoolean('LastMonth', false);

			$this->RegisterPropertyBoolean('Impulse_lBool', false);
            $this->RegisterPropertyInteger('Impulse_l', 1000);
			$this->RegisterPropertyInteger('UpdateInterval', 600);
			$this->RegisterTimer('WZ_UpdateCalculation', 0, 'WZ_updateCalculation($_IPS[\'TARGET\']);');

            $this->SetBuffer('Periods', '{}');
		
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
			
			$variableIdents = [];

            $variablePosition = 50;
			$periodsList = $this->getPeriods();
            $this->SetBuffer('Periods', json_encode($periodsList));
			}

		

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Device RECV', utf8_decode($data->Buffer));
		}

		public function getPeriods(){
		
		}
	}