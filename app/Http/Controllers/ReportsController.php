<?php

namespace App\Http\Controllers;

use App\ResetPassword;
use App\tbldataentry;
use App\tblriskfactors;
use App\tblriskindicators;
use App\tblstatepopulation;
use App\tblimpact;
use Illuminate\Http\Request;
use App\reports;

class ReportsController extends Controller
{
    public function index(){
        ini_set('max_execution_time' ,0);
        ini_set('error_reporting', 0);
        $data = [];
        $states = tblstatepopulation::select(['id','state','population'])->orderby('state','asc')->get();
        $impact_array = tblimpact::pluck('impactvalue','name');
        $impact_array['Maximum Impact'] = "3";
        $state_data = $this->getReportsData($states,'all',$impact_array);
        return response(['states' => $state_data]);
    }
    public function yearly_report(Request $request){

        $year = $request->year;
        $years = tbldataentry::groupBy('eventyear')->whereNotNull('eventyear')->pluck('eventyear')->toArray();
        if(!is_numeric($year) || !in_array($year,$years)){
            $year = 'all';
        }
        $reports = reports::where('year', $year)->get();
        return response($reports);
    }
    public function update(){
        ini_set('max_execution_time' ,0);
        ini_set('error_reporting', 0);
        
        $states = tblstatepopulation::select(['tblstatepopulation.id','tblstatepopulation.state','tblstatepopulation.population','tbltraveladvice.status'])
        ->join('tbltraveladvice', 'tbltraveladvice.state', '=', 'tblstatepopulation.state')
        ->orderby('state','asc')->get();

        $impact_array = tblimpact::pluck('impactvalue','name');
        $years = tbldataentry::groupBy('eventyear')->whereNotNull('eventyear')->pluck('eventyear');
        $years[count($years)] = 'all';
        $count_record = 0;
        foreach ($years as $year){
            $state_data = $this->getReportsData($states,$year,$impact_array);
            $count_record += $state_data;
        }
        dd($count_record .' Records updates successfully ' );
    }
    public function getReportsData($states, $year,$impact_array) {
        $count = 0;
        //Loop on all states, to get the data of each state
        foreach ($states as $state) {
            $regularity_array = [];
            //Get incidents of current state.
            $query = tbldataentry::where('location', $state->state);
            if($year != 'all'){
                $query->where('eventyear', $year);
            }
            $tblentry=  $query->get();
            $indicators = tblriskindicators::get();
            $factors = [];
            //Get Regularity values by indicators
            foreach ($indicators as $indicator){
                $incident_count = tbldataentry::where(['location' => $state->state,'riskindicators' => $indicator->indicators]);
                if($year != 'all'){
                    $incident_count->where('yy', $year);
                }
                $incident_count->count();

                $value = $incident_count / 12;

                if($value < 2) {
                    $regularity = 1;
                }elseif($value < 4) {
                    $regularity = 2;
                }else if($value >= 4) {
                    $regularity = 3;
                }
                $regularity_array[$indicator->indicators] = $regularity;
            }
            $risk_factors = [];
            foreach ($tblentry as $entry) {
                if($entry->riskindicators && array_key_exists($entry->riskindicators,$regularity_array)) {
                    if(array_key_exists($entry->riskfactors,$risk_factors)){
                        $risk_factors[$entry->riskfactors] += $impact_array[$entry->impact] + $regularity_array[$entry->riskindicators];
                    }else{
                        $risk_factors[$entry->riskfactors] = $impact_array[$entry->impact] + $regularity_array[$entry->riskindicators];
                    }
                }
            }
            $get_risk_factors = tblriskfactors::pluck('weight', 'name');
            $threat_index = 0;
            foreach ($risk_factors as $risk_key => $risk_value){
                $g_risk_factors[$risk_key] = $risk_factors[$risk_key] * $get_risk_factors[$risk_key];
                $threat_index += $g_risk_factors[$risk_key];
                if($factors[$risk_key]){
                    $factors[$risk_key] += $g_risk_factors[$risk_key];
                }else{
                    $factors[$risk_key] = $g_risk_factors[$risk_key];
                }
            }
            $th = $threat_index / $state->population;
            $threat_rate = number_format(($th * 100000), 2,'.','') * 1;

            if($threat_rate <= 20){
                $threat_level = 'Low';
                $color = 'label-success';
            }elseif ($threat_rate <= 50){
                $threat_level = 'Medium';
                $color = 'label-alert';
            }elseif ($threat_rate <= 70){
                $threat_level = 'High';
                $color = 'label-warning';
            }else{
                $threat_level = 'Very High';
                $color = 'label-danger';
            }
            $data = [
                'threat_index'  => $threat_index,
                'threat_level'  => $threat_level,
                'label'         => $color,
                'internally_disabled'   => ($factors['Internally Displaced Persons'] ?: 0 ),
                'political_instability' => $factors['Political Instability'] ?: 0,
                'emergency'             => $factors['Emergency'] ?: 0,
                'property_threats'      => $factors['Property Threats'] ?: 0,
                'personal_threats'      => $factors['Personal Threats'] ?: 0,
//                'population' => $state->population,
                'threat_rate'           => $threat_rate,
                'violent_threats'       => $factors['Violent Threats'] ?: 0,
                'status'                => $state->status
            ];
            reports::updateOrCreate(['year' => $year, 'location' => $state->state],$data);
            $count++;
        }
        return $count;
//        return $state_data;
    }
}
