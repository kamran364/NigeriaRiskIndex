<?php

namespace App\Http\Controllers;

use App\reports;
use App\ResetPassword;
use App\User;
use DateTime;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use App\tbldataentry;
use App\tblweeklydataentry;
use App\tblanalysis;
use App\tblriskfactors;
use App\tblstatepopulation;
use App\tblriskindicators;
use App\tblscrappingdetails;
use App\tblcountry;
use App\tblriskindicatorscat;
use App\tblmysubscriptions;
use App\tbllga;
use Exception;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;

class IncidentController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // Get Chartable Data for 10 Days
    public function getChartableData()
    {
    	return tbldataentry::orderBy('id','desc')->take(10)->get();
    }
    // Get Weekly Incidents Data for 10 Days
    public function getWeeklyIncidentsData()
    {
//    	return tblweeklydataentry::Where('News','<>','Yes')->orderBy('id','desc')->take(10)->get();
    	return tbldataentry::orderBy('id','desc')->take(10)->get();
    }
    // Get Weekly News Data
    public function getWeeklyNewsData()
    {
    	return tblanalysis::orderBy('id','desc')->take(10)->get();
    }
    // Get Ridk Factors List
    public function getRiskFactors()
    {
        return tblriskfactors::select(['id','name','weight'])->orderby('name','asc')
        ->with('getRiskIndicators')->get();
    }
    public function getIndicator($id){
        $data[0] =  tblriskindicators::where('ID', $id)->select('indicators', 'factors','weight')->first();
        $data[1] =  tblriskfactors::pluck('name');
        $indicator = tblriskindicators::where('factors', $data[0]['factors'])->where('ID', '<>', $id)->sum('weight');
        $max_indicator = tblriskfactors::where('name', $data[0]['factors'])->pluck('weight');
        $data[2] = $max_indicator[0] - $indicator;
        return response($data);
    }
    public function getFactor($id){
        $data =  tblriskfactors::where('ID', $id)->first();
        return response($data);
    }
    public function editFactor(Request $request){
        $factor = $request->name;
        $weight = $request->weight;
        $id = $request->id;
        return tblriskfactors::where('ID', '=', $id)->update(['name' => $factor,'weight' => $weight]);
    }
    public function addFactor(Request $request){
        $obj = new tblriskfactors();
        $obj->name = $request->factor_name;
        $obj->weight = $request->weight;
        //        $obj->author =  auth('api')->user()->email;
        $obj->datecreated =  Carbon::now()->format('M d, Y');
        $obj->timecreated =  Carbon::now()->format('H:m:i A');
        $obj->save();
        return response('1');
    }
    public function add_indicator(Request $request){
        $obj = new tblriskindicators();
        $obj->factors = $request->factors;
        $obj->indicators = $request->indicators;
        $obj->weight = $request->weight;
//        $obj->author =  auth('api')->user()->email;
        $obj->datecreated =  Carbon::now()->format('M d, Y');
        $obj->timecreated =  Carbon::now()->format('H:m:i A');
        $obj->save();
        return response('1');
    }
    public function factor_list(){
        $factors =  tblriskfactors::all();
        foreach ($factors as $f){
            $indicator = tblriskindicators::where('factors', $f->name)->sum('weight');
            $data[1][$f->name] = $f->weight - $indicator;
            $data[0][$f->name] = $f->name;
        }
        return response($data);
    }
    //Move Indicators
    public function moveIndicators(Request $request){
        $factor = $request->factors;
        $weight = $request->weight;
        $indicators = $request->indicators;
        return tblriskindicators::where('indicators', '=', trim($indicators))->update(['factors' => $factor,'weight' => $weight]);
    }
    //Delete Risk indicators
    public function deleteIndicator($id) {
        return tblriskindicators::where('ID', $id)->delete();
    }
    // Filtered Risk Indicators
    public function getFilteredRiskIndicators(Request $request)
    {
        return tblriskindicators::select(['id','indicators'])
        ->where(function ($query) use ($request)
        {
            if(!empty($request->factor))
                $query->where('factors', '=', $request->factor);
        })->get();
    }
    // Get States
    public function getStates()
    {
        return tblstatepopulation::select(['id','state'])->orderby('state','asc')->get();
    }
    // Get LGAs
    public function getLgas(Request $request)
    {
        $state = trim($request->state);
        return tbllga::select(['ID', 'LGA'])->where('State', $state)->get();    
    } 
    // Search Incidents
    public function searchIncidents(Request $request)
    {

        return tbldataentry::select(['ID', 'caption', 'eventdateToUse'])
            ->where(function($query) use ($request) {
                $query->where('caption','Like','%'.$request->caption.'%');
                // If selected some state
                if (!empty($request->states))
                    $query->where('location','=', $request->states);
            })
            ->orderBy('eventyear', 'desc')
            ->get();
    }
    // Get Selected Incident Data
    public function getSelectedIncidentData(Request $request)
    {
        return tbldataentry::where('id', $request->id)->first();
    }
    // Get Incident Data By Event ID
    public function getIncidentbyEventId(Request $request)
    {
        return tblweeklydataentry::where('eventid', $request->eventid)->first();
    }
    //
    public function getENewspapers(Request $request)
    {
        return tblscrappingdetails::select(['ID','state', 'website', 'newspaper', 'publisher'])
        ->orderBy('state', 'asc')
        ->get();
    }
    // Get Country List
    public function getCountryList(Request $request)
    {
        return tblcountry::select(['ID','name'])->orderBy('name','asc')->get();
    }
    // Function to add new e newspaper scrape site
    public function addNewScrapeSite(Request $request)
    {
        try{
            $data = [
                'state'         => $request->state,
                'country'       => $request->country,
                'newspaper'     => $request->newspaper,
                'publisher'     => $request->publisher,
                'website'       => $request->website,
                'author'        => 'test',
                'datecreated'   => Carbon::now()->toFormattedDateString(),
            ];
            
            $newEnewspaper = tblscrappingdetails::create($data);
            return $newEnewspaper;
        }
        catch(Exception $exception)
        {
            throw new Exception($exception);
            
        }
    }

    public function getManageAdvisory(Request $request)
    {
        $years = tbldataentry::groupBy('eventyear')->whereNotNull('eventyear')->pluck('eventyear');
        $years[count($years)] = 'all';
        $advisory_data = [];
        $states = tblstatepopulation::select(['id','state','population','latitude','longitude'])->orderby('state','asc')->get();
        
        foreach ($states as $state) {
            $year_array = [];
            $year_array['state'] = $state->state;
            $year_array['lat'] = $state->latitude;
            $year_array['long'] = $state->longitude;
//            $year_array['short_code'] = $state->short_code;
            $year_array['lga_count'] = tbllga::where('State', $state->state)->count();
            foreach ($years as $year){
                $manage_advisory = reports::select('threat_level','threat_rate')->where(['year' => $year,'location' => $state->state])->first();
                $year_array['md'.$year] = $manage_advisory->threat_level;

                if($year == 'all'){
                    $year_array['threat_rate'] = $manage_advisory->threat_rate;
                    $rate = 0;
                    if($manage_advisory->threat_level == 'Very High'){
                        $rate = 3;
                    }
                    if ($manage_advisory->threat_level == 'High'){
                        $rate = 2;
                    }
                    if($manage_advisory->threat_level == 'Medium'){
                        $rate = 1;
                    }
                    if($manage_advisory->threat_level == 'Low'){
                        $rate = 0;
                    }
                    $year_array['threat_map_level'] = $rate;
                }
            }
            $advisory_data[] = $year_array;
        }
        return $advisory_data;


        // $manageAdvisoryArr = tblstatepopulation::select(DB::raw("
        //         tblstatepopulation.state, 
        //         count(tbllga.LGA) as tbllgaCount,
        //         tblmyreportall.Threat_level as rsTL,
        //         tblmyreport2014.Threat_level as rsTL2014,
        //         tblmyreport2015.Threat_level as rsTL2015,
        //         tblmyreport2016.Threat_level as rsTL2016,
        //         tblmyreport2017.Threat_level as rsTL2017,
        //         tblmyreport2018.Threat_level as rsTL2018,
        //         tbltraveladvice.status as adviceStatus"
        //     ))
        //     ->join('tblmyreportall', 'tblstatepopulation.state', '=', 'tblmyreportall.location')
        //     ->join('tblmyreport2014', 'tblstatepopulation.state', '=', 'tblmyreport2014.location')
        //     ->join('tblmyreport2015', 'tblstatepopulation.state', '=', 'tblmyreport2015.location')
        //     ->join('tblmyreport2016', 'tblstatepopulation.state', '=', 'tblmyreport2016.location')
        //     ->join('tblmyreport2017', 'tblstatepopulation.state', '=', 'tblmyreport2017.location')
        //     ->join('tblmyreport2018', 'tblstatepopulation.state', '=', 'tblmyreport2018.location')
        //     ->join('tbllga', 'tblstatepopulation.state', '=', 'tbllga.state')
        //     ->leftjoin('tbltraveladvice', 'tblstatepopulation.state', '=', 'tbltraveladvice.state')
        //     ->groupby('tblstatepopulation.state')
        //     ->get();
        //     return $manageAdvisoryArr;
    }
    // Get Update Casualties Event
    // This function will return an event where casualities count is NULL.
    public function getUpdateCasualtiesEvent(Request $request)
    {
//        dd($request);
        return tbldataentry::select(['eventid', 'Casualties_pointer', 'id',
            'Casualties_count', 'injuries_count', 'caption',
            'content', 'tbldataentry.location', 'tbldataentry.riskfactors', 'tbldataentry.riskindicators',
            'tbldataentry.Impact', 'accused', 'victim', 'Injuries_pointer',
            'image'])
//        ->join('tbldataentry', 'tbldataentry.eventid','=','eventid')
        ->where('eventyear','=',(Carbon::now())->year)
        ->where('news','=','No')
        ->where('Casualties_pointer','=',NULL)
//        ->orwhere('Casualties_pointer','=','')
        ->orderBy('id', 'desc')
        ->first();

    }
    // Update Casualities
    public function updateCasualities(Request $request)
    {
//        dd($request);
        try{

            tbldataentry::where('eventid','=', $request->eventid)->update($request->all());
            return tbldataentry::where('eventid','=', $request->eventid)->update($request->all(), 200);

        }
        catch(Exception $exception)
        {
            throw new Exception("Error while updating casualities.", 1);
        }
    }
    // Get Risk Favtor Sub Categories
    public function getFactorSubCat(Request $request)
    {
        return tblriskindicatorscat::select(['subCategory'])->where('factors','=',trim($request->factor))->get();
    }
    public function add_incidents(Request $request)
    {

        try{
            $data = $request->input();

            $lat_long = [];
            $lat_long['latitude'] = '';
            $lat_long['longitude'] = '';
            
            if($data['state'] && $data['lga']) {
                $lat_long = tbllga::where(['State' => $data['state'],'LGA' => $data['lga']])->first();
            }
//            $date_string = "2012-02-02";
            $date_string = $data['event_year'].'-'.$data['event_month'].'-'.$data['event_day'];
            $week = date("W", strtotime($date_string));

            $now = Carbon::now();
            $unique_code = $now->format('YmdHisu');
            $imageName = '';
            if($request->file('incident_img'))
                $imageName = $request->file('incident_img')->storeAs('images/incidents', $unique_code.'.png');
            
            $table_data_entry = [
              'eventid' => $unique_code,
              'location' => $data['state'],
              'lga' => $data['lga'],
              'lga_lat' => $lat_long['latitude'],
              'lga_long' => $lat_long['longitude'],
              'eventdate' => $data['event_month'].'/'.$data['event_day'].'/'.$data['event_year'],
              'eventdateToUse' => $data['event_year'].'-'.$data['event_month'].'-'.$data['event_day'],
              'dd' => $data['event_day'],
              'week' => $week,
              'eventday' => $data['event_day'],
              'mm' => $data['event_month'],
              'eventmonth' => $data['event_month'],
              'YY' => $data['event_year'],
              'eventyear' => $data['event_year'],
              'riskfactors' => $data['riskFactor1'],
              'riskindicators' => $data['riskFactor1_indicator'],
              'subcategory' => $data['riskFactor1_subCat'],
              'impact' => $data['impact'],
              'author' => auth('api')->user()->email,
              'datecreated' => Carbon::now()->format('M d, Y'),
              'auditauthor' => auth('api')->user()->email,
              'auditdatecreated' => Carbon::now()->format('M d, Y'),
              'audittimecreated' => Carbon::now()->format('h:i:s a'),
              'month_pro' => Carbon::now()->format('M'),
              'eventtime' => $data['occurance_time'],
              'month_pro2' => Carbon::now()->format('M'),
              'datasense' => $data['datasense'],
              'Casualties_count' => $data['deaths_count'],
              'Injuries_count' => $data['injuries_count'],
              'latitude' => $data['latitude'], // Incidents City LAT
              'longitude' => $data['longitude'], // Incidents City LONG
              'city' => $data['lga'],
              'accused' => $data['accused'],
              'victim' => $data['victim'],
              'image' => 'storage/app/'.$imageName,
              'hashtag' => $data['hashtag'],
              'caption' => $data['caption'],
              'content' => $data['incident_summary'],
              'news' => 'No',
              // New added fields
              'extended' => $data['extended'],
              'attack_status' => $data['attack_status'],
              'target' => $data['target'],
              'target_type' => $data['target_type'],
              'target_details' => $data['target_details'],
              'attack_group' => $data['attack_group'],
              'attack_group_type' => $data['attack_group_type'],
              'target_motive' => $data['target_motive'],
              'weapon' => $data['weapon'],
              'weapon_type' => $data['weapon_type'],
              'property_extent' => $data['property_extent'],
              'property_extent_detail' => $data['property_extent_detail'],
              'attack_days' => $data['attack_days'],
              'ransom' => $data['ransom'],
              'ransom_amount' => $data['ransom_amount'],
              'kidnap_outcome' => $data['kidnap_outcome'],
              'kidnap_details' => $data['kidnap_details'],
              'incident_sources' => $data['incident_sources'],
              'incident_dbsources' => $data['incident_dbsources'],
              'claim_mode' => $data['claim_mode'],
            ];
        
        $tbl_entry = new tbldataentry();
        $result1 = $tbl_entry->create($table_data_entry);
        if($result1) {
            return response(['statusText' => 'Incident added successfully.','status' => 201]);
        }
        else {
            return response(['statusText' => 'Something went wrong.','status' => 500]);
        }
    } catch(Exception $exception) {
        throw new Exception($exception);
    }
}
public function deleteChartableData($id){
    return tbldataentry::where('ID',$id)->delete();
}
public function deleteWeeklyIncidentsData($id){
    return tbldataentry::where('ID',$id)->delete();
}
public function deleteENewspapers($id){
    return tblscrappingdetails::where('ID',$id)->delete();
}
    // Get Record for Update Coordinates page
public function getUpdateCoordinateRecord()
{

    return tbldataentry::select(['tbldataentry.eventid', 'tbldataentry.Casualties_pointer', 'tbldataentry.Casualties_count',
        'tbldataentry.injuries_count', 'tbldataentry.caption', 'tbldataentry.content', 'tbldataentry.Longitude', 'tbldataentry.Latitude',
        'tbldataentry.location', 'tbldataentry.riskfactors', 'tbldataentry.riskindicators', 'tbldataentry.Impact', 'tbldataentry.accused',
        'tbldataentry.victim', 'tbldataentry.Injuries_pointer','tbldataentry.image'])
//    ->join('tbldataentry', 'tbldataentry.eventid','=','tblweeklydataentry.eventid')
    ->where('tbldataentry.news','=','No')
    ->where(function($longitude) {
        $longitude->where('tbldataentry.longitude', Null)
        ->orWhere('tbldataentry.longitude', '')
        ->orWhere('tbldataentry.longitude', '1.123456789');
    })
    ->where(function($latitude) {
        $latitude->where('tbldataentry.latitude', '')
        ->orWhere('tbldataentry.latitude', Null)
        ->orWhere('tbldataentry.latitude', '1.123456789');
    })
    ->orderBy('tbldataentry.ID','desc')
        ->first();
}
    //Get All non cordinates records
public function getUpdateCoordinateList(){

    return tbldataentry::select(['caption', 'eventid'])
//    ->join('tbldataentry', 'tbldataentry.eventid','=','tblweeklydataentry.eventid')
    ->where('tbldataentry.news','=','no')
    ->where(function($longitude) {
        $longitude->where('longitude', Null)
        ->orWhere('longitude', '')
        ->orWhere('longitude', '1.123456789');
    })
    ->where(function($latitude) {
        $latitude->where('latitude', '')
        ->orWhere('latitude', Null)
        ->orWhere('latitude', '1.123456789');
    })
    ->orderBy('ID','desc')->get();
}
    //Get one non cordinates records
public function getOneIncidentData(Request $request){
//        dd($request->request);
    $id = $request->input('eventid');
    return tbldataentry::select(['eventid', 'image', 'Casualties_pointer', 'Casualties_count',
        'injuries_count', 'caption', 'content', 'Longitude', 'Latitude',
        'location', 'riskfactors', 'riskindicators', 'Impact',
        'accused', 'victim', 'Injuries_pointer'])
//    ->join('tbldataentry', 'tbldataentry.eventid','=','tblweeklydataentry.eventid')
    ->where('news','=','No')
    ->where('eventid',$id)
    ->orderBy('ID','desc')->first();
}

    // Get Data Authors List
public function GetAuthorsList()
{
    $query =  tbldataentry::distinct('tbldataentry.auditauthor')
    ->select(['tbldataentry.auditauthor','tblusers.fname','tblusers.lname'])
    ->join('tblusers', 'tblusers.usernamerisk','=','tbldataentry.auditauthor')
    ->Where('tbldataentry.auditauthor','<>','')
    ->get();
    return $query;
}

public function getReviewDataEntries(Request $request)
{
    $entries_list = array();

    $incidents = tblstatepopulation::select(DB::raw('tblstatepopulation.state, tblstatepopulation.capital, count(tbldataentry.ID) as incidentsCount, tbldataentry.eventyear'))
    ->leftjoin('tbldataentry', 'tbldataentry.location', '=', 'tblstatepopulation.state')
    ->where('tbldataentry.eventyear', '>=', '2017')
    ->groupby('tbldataentry.eventyear', 'tblweeklydataentry.location')
    ->orderby('tbldataentry.location', 'asc')
    ->orderby('tbldataentry.eventyear', 'asc')->get();
    
        // Prepare incidents count array
    foreach ($incidents as $key => $value) {
        
        $entries_list[$value->state]['capital'] = $value->capital;
        $entries_list[$value->state][$value->dyear]['incident'] = $value->incidentsCount;
            // Incidents total of each State
        if(empty($entries_list[$value->state]['incidentTotal']))
            $entries_list[$value->state]['incidentTotal'] = $value->incidentsCount;
        else
            $entries_list[$value->state]['incidentTotal'] += $value->incidentsCount;
    }
        //  Get Total News of each state
    $incidentsNews = tblstatepopulation::select(DB::raw('tblstatepopulation.state, count(tblweeklydataentry.ID) as newsCount'))
    ->leftjoin('tblweeklydataentry', 'tblweeklydataentry.location', '=', 'tblstatepopulation.state')
    ->where('tblweeklydataentry.news', '=', 'Yes')
    ->groupby('tblweeklydataentry.location')
    ->orderby('tblweeklydataentry.location', 'asc')
    ->get();

    foreach ($incidentsNews as $key => $value) {
        $entries_list[$value->state]['newsCount'] = $value->newsCount;
    }

        // Get charted count of each state
    $charted = tblstatepopulation::select(DB::raw('tblstatepopulation.state, tblstatepopulation.capital, tbldataentry.eventyear, count(tbldataentry.ID) as chartableCount'))
    ->leftjoin('tbldataentry', 'tbldataentry.location', '=', 'tblstatepopulation.state')
    ->where('tbldataentry.eventyear', '>=', '2017')
    ->groupby('tbldataentry.location', 'tbldataentry.eventyear')
    ->orderby('tbldataentry.location', 'asc')
    ->orderby('tbldataentry.eventyear', 'asc')->get();

        // Prepare chartable count array
    foreach ($charted as $key => $value) {
        $entries_list[$value->state][$value->eventyear]['charted'] = $value->chartableCount;
            // Charted total of each State
        if(empty($entries_list[$value->state]['chartedTotal']))
            $entries_list[$value->state]['chartedTotal'] = $value->chartableCount;
        else
            $entries_list[$value->state]['chartedTotal'] += $value->chartableCount;
    }

    return $entries_list;

}
    // Get incidents count for each year 
public function getIncidentsCount(Request $request)
{
    $year = $request->year;

    $incident_list = array();
    $incidents = tbldataentry::select(DB::raw('location, riskfactors, count(id) as total'))
    ->where('eventyear', '=', $year)
    ->groupby('location','riskfactors')
    ->orderby('location','asc')
    ->orderby('riskfactors','asc')
    ->get();

    foreach ($incidents as $key => $value) {

        $incident_list[$value->location][strtolower(str_replace(' ', '_', $value->riskfactors))] = $value->total;
        if(empty($incident_list[$value->location]['total']))
            $incident_list[$value->location]['total'] = $value->total;
        else
            $incident_list[$value->location]['total'] += $value->total;

    }

    return $incident_list;
}
    // Get list of subscribed states
public function getSubscribedStates()
{
    return tblmysubscriptions::select('state')->orderby('state','asc')->get();   
}

// Get News Feeds
public function getNewsFeed(Request $request)
{
//    dd($request->year);
    return tbldataentry::join('users', 'users.email','=', 'tbldataentry.author')
    ->select(DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'passport', 'image','datecreated' ,'eventdate','caption','eventid',
        'content','location','tbldataentry.city','tbldataentry.LGA','riskfactors','riskindicators','casualties_count','injuries_count')
    ->where(function($query) use ($request) {
        $query->where('eventyear','=', $request->year);
//        $query->where('news','<>', 'Yes');

        // If selected some state
        if ($request->location != 'all')
            $query->where('location','=', $request->location);
        // If selected some risk factor
        if ($request->factor != 'all')
            $query->where('riskfactors','=', $request->factor);
        // If selected some risk factor
        if ($request->riskindicator != 'all')
            $query->where('riskindicators','=', $request->riskindicator);
    })
    ->orderby('tbldataentry.id', 'desc')
    ->paginate(15);
}

// Get all incidents count by year
public function getIncidentsCountByYear()
{
    $incident_list = array();
    $incidents = tbldataentry::select(DB::raw('trim(location) as location ,eventyear, count(id) as total, count(Casualties_count) as casuality'))
    ->groupby('location','eventyear')
    ->where('eventyear', '>', 2017)
    ->orderby('eventyear','desc')
    ->orderby('location','asc')
    ->get();

    foreach ($incidents as $key => $value) {
        $incident_list[$value->location][$value->eventyear] = "<span style='color:#7CB5EC;'>".$value->total ."</span>|<span style='color:#434348;'>". $value->casuality ."</span>";
    }

    return $incident_list;
}
    // Get Compare States Stats
public function fun_compareStates(Request $request)
{
    $factors_list = array();
    $incident_list = array();
        // Risk Factors
    $riskFactors_count = tbldataentry::select(DB::raw('trim(location) as location, riskfactors, count(*) as totalFactors'))
    ->whereIn('location',[$request->state1, $request->state2])
    ->where('eventyear', $request->year)
    ->groupBy('location', 'riskfactors')
    ->orderby('location','asc')->get();

        // Risk Indicators
    $riskIndicators_count = tbldataentry::select(DB::raw('trim(location) as location, eventmonth, eventyear, count(*) as total'))
    ->whereIn('location',[$request->state1, $request->state2])
    ->where('eventyear', $request->year)
    ->groupBy('location', 'eventmonth')
    ->orderby('eventmonth','asc')
    ->orderby('location','asc')->get();

//    dd($riskFactors_count);


        // prepare risk factors array
    foreach ($riskFactors_count as $key => $value) {

        if(trim($value->location) == trim($request->state1))
            $factors_list[$request->state1][$value->riskfactors] = $value->totalFactors;
        else
            $factors_list[$request->state2][$value->riskfactors] = $value->totalFactors;
    }
        // prepare risk factors array
    foreach ($riskIndicators_count as $key => $value) {

        if(trim($value->location) == trim($request->state1))
            $factors_list[$request->state1][$value->eventmonth] = $value->total;
        else
            $factors_list[$request->state2][$value->eventmonth] = $value->total;
    }


    return $factors_list;

}
    // Get Visualize Data for States - Visualize Data
public function fun_getStateVisualizeData(Request $request)
{
//    dd($request);
        // Only Run in case State Filter is applied - For Main Dashboard
    if(!empty($request->state))
    {
        $stateData_pre = tblstatepopulation::select(DB::raw("tblservice_companies.type, count(tblservice_companies.type) typesTotal,tblstatepopulation.Overview,
         tblstatepopulation.capital, tblstatepopulation.region, tblstatepopulation.zipcode, tblstatepopulation.population_est, tblstatepopulation.male_est, tblstatepopulation.female_est"))
        ->leftjoin('tblservice_companies', 'tblservice_companies.state', '=', 'tblstatepopulation.state')
        ->where('tblstatepopulation.state', '=', $request->state)
        ->groupby('tblservice_companies.type')
        ->get();
    }

        // Get Incicdents Locations, Latitude and Longitude for cluster map counters.
    $incidents_location = tbldataentry::select(['lga_lat', 'lga_long', 'caption', 'eventid'])
    ->where(function ($query) use ($request) {

        if(!empty($request->state))
            $query->where('location', '=', $request->state);

        if(!empty($request->month_txt) && $request->month_txt != 'all')
            $query->where('eventmonth', '=', $request->month_txt);
        if(!empty($request->day_txt) && $request->day_txt != 'all')
            $query->where('eventday', '=', $request->day_txt);

        if(!empty($request->year) && $request->year != 'all')
            $query->where('eventyear', '=', $request->year);
        if(!empty($request->risk_factor) && $request->risk_factor != 'all')
            $query->where('riskfactors', '=', $request->risk_factor);
        if(!empty($request->risk_indicator) && $request->risk_indicator != 'all')
            $query->where('riskindicators', '=', $request->risk_indicator);
    })
    ->where('lga_long', '<>', null)
    ->get();
//    dd($incidents_location);
        // Get Riskfactors and riskindicators data
    $riskFactors = tbldataentry::select(DB::raw('riskfactors, riskindicators, count(*) as incidentsCount'))
    ->where(function ($query) use ($request) {
        if(!empty($request->month_txt) && $request->month_txt != 'all')
            $query->where('eventmonth', '=', $request->month_txt);
        if(!empty($request->day_txt) && $request->day_txt != 'all')
            $query->where('eventday', '=', $request->day_txt);
        if(!empty($request->state))
            $query->where('location', '=', $request->state);
        if(!empty($request->year) && $request->year != 'all' )
            $query->where('eventyear', '=', $request->year);
        if(!empty($request->risk_factor) && $request->risk_factor != 'all')
            $query->where('riskfactors', '=', $request->risk_factor);
        if(!empty($request->risk_indicator) && $request->risk_indicator != 'all')
            $query->where('riskindicators', '=', $request->risk_indicator);
    })
    ->groupby('riskfactors', 'riskindicators')
    ->orderby('riskfactors', 'ASC')
    ->orderby('riskindicators', 'ASC')
    ->get();

        // Only Run in case State Request is applied - For Main Dashboard
    if(!empty($request->state))
    {
        $stateData = [
            'Overview' => '',
            'capital' => $stateData_pre[0]->capital,
            'female_est' => $stateData_pre[0]->female_est,
            'population_est' => $stateData_pre[0]->population_est,
            'region' => $stateData_pre[0]->region,
            'type' => array(),
            'zipcode' => $stateData_pre[0]->zipcode
        ];

//        dd($stateData_pre);

            // State Basic Info
        foreach ($stateData_pre as $key => $value) {
            $stateData['type'][strtolower(str_replace(' ', '_', trim($value['type'])))] = $value['typesTotal'];
            $stateData['Overview'] = strip_tags($value['Overview']);
        }
    }

        // Preapare incidents locations
    $stateData['incidents_positions'] = [];
    foreach ($incidents_location->toArray() as $key => $value) {
        $stateData['incidents_positions'][] = [
            'lat'   => $value['lga_lat'],
            'long'  => $value['lga_long'],
            'caption'  => $value['caption'],
            'eventid'  => $value['eventid']
        ];
    }

        // Prepare riskfactors list
    $stateData['riskfactors'] = [];
    foreach ($riskFactors->toArray() as $key => $value) {

        $stateData['riskfactors'][trim($value['riskfactors'])][trim($value['riskindicators'])] = $value['incidentsCount'];
            // Riskfactors Total Incidents
        if(!array_key_exists('total_incidents' ,$stateData['riskfactors'][$value['riskfactors']]))
            $stateData['riskfactors'][trim($value['riskfactors'])]['total_incidents'] = $value['incidentsCount'];
        else
            $stateData['riskfactors'][trim($value['riskfactors'])]['total_incidents'] += $value['incidentsCount'];
    }
    return $stateData;
}
    // Get State Lga's Riskfactors incidents
public function getLgaFactorsStats(Request $request)
{
    $getLgaFactorsStats = tbllga::select(DB::raw('tbldataentry.riskfactors, tbllga.LGA, COUNT(*) as total'))
    ->leftjoin('tbldataentry', function($join) {
        $join->on('tbldataentry.location', '=', 'tbllga.State');
        $join->on('tbldataentry.lga', '=', 'tbllga.LGA');
    })
    ->where(function ($query) use ($request)
    {
        if(!empty($request->state))
            $query->where('location', '=', $request->state);
        if(!empty($request->year) && $request->year != 'all')
            $query->where('eventyear', '=', $request->year);
    })
    ->groupby('tbllga.LGA', 'tbldataentry.riskfactors')
    ->orderby('tbllga.LGA', 'asc')
    ->get();

    $temp_lgaFactorsStats = [];
    $temp_lgaFactorsStats['RF'] = [];
        // prepare lga factors incidents data 
    foreach ($getLgaFactorsStats->toArray() as $key => $value) {
        $temp_lgaFactorsStats['LGA'][$value['LGA']][trim(str_replace(' ', '', $value['riskfactors']))] = $value['total'];
            // Calculate riskfactors total
            // if(!array_key_exists(trim(str_replace(' ', '', $value['riskfactors'])), $temp_lgaFactorsStats['RF']['total']))
        if(!array_key_exists('total', $temp_lgaFactorsStats['RF']))
            $temp_lgaFactorsStats['RF']['total'][trim(str_replace(' ', '', $value['riskfactors']))] = $value['total'];
        else if(!array_key_exists(trim(str_replace(' ', '', $value['riskfactors'])), $temp_lgaFactorsStats['RF']['total']))
            $temp_lgaFactorsStats['RF']['total'][trim(str_replace(' ', '', $value['riskfactors']))] = $value['total'];
        else
            $temp_lgaFactorsStats['RF']['total'][trim(str_replace(' ', '', $value['riskfactors']))] += $value['total'];

    }
    return $temp_lgaFactorsStats;
}
/* Function to get Week number by date for incidents  */
public function getWeekNumber(Request $request){
    $date = $request->input('date');
    return Carbon::parse($date)->weekOfYear;
}
public function GetStateDashboard(Request $request) {
//    dd($request);

    $stateData_pre = tblstatepopulation::select(['state', 'region', 'zipcode','population','male','female','CAPITAL',
        DB::Raw('count(Casualties_count) as casualties, count(Injuries_pointer) as injuries')])
    ->join('tbldataentry','tbldataentry.location','=', 'tblstatepopulation.state')
    ->where('tblstatepopulation.state', '=', $request->state)
    ->first();
    
    // Get Risk factor Stats  
    $get_riskfactor = tbldataentry::select(DB::raw('riskfactors, count(*) riskfactor_count'))
    ->where(function ($query) use ($request)
    {

        if(!empty($request->month_txt) && $request->month_txt != 'all')
            $query->where('eventmonth', '=', $request->month_txt);
        if(!empty($request->day_txt) && $request->day_txt != 'all')
            $query->where('eventday', '=', $request->day_txt);

        if(!empty($request->state))
            $query->where('location', $request->state);
        if(!empty($request->year) && $request->year != 'all')
            $query->where('eventyear', $request->year);
        if(!empty($request->risk_factor) && $request->risk_factor != 'all')
            $query->where('riskfactors', '=', $request->risk_factor);
        if(!empty($request->risk_indicator) && $request->risk_indicator != 'all')
            $query->where('riskindicators', '=', $request->risk_indicator);
    })
    ->groupby('riskfactors')->get();

//     Latest 5 News Lists for Dashboard
    $get_news = tbldataentry::select(['caption', 'location', 'eventdateToUse'])
    ->where(function ($query) use ($request){

        if(!empty($request->month_txt) && $request->month_txt != 'all')
            $query->where('eventmonth', '=', $request->month_txt);
        if(!empty($request->day_txt) && $request->day_txt != 'all')
            $query->where('eventday', '=', $request->day_txt);
        if(!empty($request->state))
            $query->where('location', $request->state);
        if(!empty($request->risk_factor) && $request->risk_factor != 'all')
            $query->where('riskfactors', '=', $request->risk_factor);
        if(!empty($request->risk_indicator) && $request->risk_indicator != 'all')
            $query->where('riskindicators', '=', $request->risk_indicator);
//        $query->where('news', null);
    })->orderBy('id','desc')->limit(10)->get();
//    dd($get_news);

    // Get Incidents count for current year - Selected State
    $incidents_count = tbldataentry::select(DB::raw('trim(location) as location, eventmonth, eventyear, count(*) as total'))
    ->where(function ($query) use ($request) {

        if(!empty($request->month_txt) && $request->month_txt != 'all')
            $query->where('eventmonth', '=', $request->month_txt);
        if(!empty($request->day_txt) && $request->day_txt != 'all')
            $query->where('eventday', '=', $request->day_txt);

        if(!empty($request->state))
            $query->where('location', $request->state);
        if(!empty($request->year) && $request->year != 'all')
            $query->where('eventyear', $request->year);
        if(!empty($request->risk_factor) && $request->risk_factor != 'all')
            $query->where('riskfactors', '=', $request->risk_factor);
        if(!empty($request->risk_indicator) && $request->risk_indicator != 'all')
            $query->where('riskindicators', '=', $request->risk_indicator);
    })
    ->groupBy('location', 'eventmonth')
    ->orderby('eventmonth','asc')
    ->orderby('location','asc')->get();

    $factors_list = array();

        // prepare array
    foreach ($incidents_count as $key => $value) {
        
        $factors_list[$value->location][$value->eventmonth] = $value->total;
    }

    return [
        'stats'         => $stateData_pre, 
        'news'          => $get_news, 
        'riskfactors'   => $get_riskfactor,
        'factors_list'  => $factors_list
        // 'reports'       => $get_reports,
        // 'incidents'     => $get_incidents, 
    ];
}

    // Get Compare States Stats
public function dashbaord_States(Request $request)
{
    $factors_list = array();
    $incident_list = array();
        // Risk Factors
    $riskFactors_count = tbldataentry::select(DB::raw('trim(location) as location, riskfactors, count(*) as totalFactors'))
//            ->whereIn('location',[$request->state1, $request->state2])
//            ->where('eventyear', $request->year)
    ->where('riskfactors', $request->risk_factor)
    ->groupBy('location', 'riskfactors')
    ->orderby('location','asc')->get();

        // Risk Indicators
    $riskIndicators_count = tbldataentry::select(DB::raw('trim(location) as location, eventmonth, eventyear, count(*) as total'))
//            ->whereIn('location',[$request->state1, $request->state2])
    //            ->where('eventyear', $request->year)
    ->where('riskfactors', $request->risk_factor)
    ->groupBy('location', 'eventmonth')
    ->orderby('eventmonth','asc')
    ->orderby('location','asc')->get();
    $risk_factor = $risk_indicator = [];
    foreach ($riskFactors_count as $rc){
     if($rc['location'])
        $risk_factor[] = ['location' => $rc['location'], 'factor' =>  $rc['totalFactors']];
}
foreach ($riskIndicators_count as $rc){
    if($rc['location'])
        $risk_indicator[] = ['location' => $rc['location'], 'indicator' =>  $rc['total']];
}
//        dd($risk_indicator);
return ['rf' => $risk_factor, 'ri' => $risk_indicator];
//        // prepare risk factors array
//        foreach ($riskFactors_count as $key => $value) {
//
//            if(trim($value->location) == trim($request->state1))
//                $factors_list[$request->state1][$value->riskfactors] = $value->totalFactors;
//            else
//                $factors_list[$request->state2][$value->riskfactors] = $value->totalFactors;
//        }
//        // prepare risk factors array
//        foreach ($riskIndicators_count as $key => $value) {
//
//            if(trim($value->location) == trim($request->state1))
//                $factors_list[$request->state1][$value->eventmonth] = $value->total;
//            else
//                $factors_list[$request->state2][$value->eventmonth] = $value->total;
//        }

        // dd($factors_list);

        // $result = [
        //         "riskfactor" => [
        //             $factors_list[$request->state1],
        //             $factors_list[$request->state2]
        //         ],
        //         "riskindicator" => []
        // ];

}
public function get_dashboard_data(Request $request) {
    $risk_factor_input = $request->risk_factor;
    $risk_indicator_input = $request->risk_indictor;
    $year_input = $request->year;
    $stateData_pre = tblstatepopulation::select(DB::raw("tblservice_companies.type, count(tblservice_companies.type) typesTotal,tblstatepopulation.Overview, 
    tblstatepopulation.capital, tblstatepopulation.region, tblstatepopulation.zipcode, tblstatepopulation.population_est, tblstatepopulation.male_est, tblstatepopulation.female_est"))
    ->leftjoin('tblservice_companies', 'tblservice_companies.state', '=', 'tblstatepopulation.state')
//            ->where('tblstatepopulation.state', '=', $request->state)
    ->groupby('tblservice_companies.type')
    ->get();
        // Get Incicdents Locations, Latitude and Longitude for cluster map counters.
    $incidents_location = tbldataentry::select(['lga_lat', 'lga_long']);
    if($risk_factor_input != 'all'){
        $incidents_location->where('riskfactors',$risk_factor_input);
    }
    if($risk_indicator_input != 'all'){
        $incidents_location->where('riskindicators',$risk_factor_input);
    }
    if($year_input != 'all'){
        $incidents_location->where('eventyear',$year_input);
    }
    $incidents_location->where('lga_long', '<>', null)
    ->get();
        // Get Riskfactors and riskindicators data
//        $riskFactors = tbldataentry::select(DB::raw('riskfactors, riskindicators, count(*) as incidentsCount'));
    $riskFactors = tbldataentry::select('riskfactors', 'riskindicators')
    ->selectRaw('count(*) as incidentsCount');
    if($risk_factor_input != 'all'){
        $riskFactors->where('riskfactors',$risk_factor_input);
    }
    if($risk_indicator_input != 'all'){
        $riskFactors->where('riskindicators',$risk_factor_input);
    }
    if($year_input != 'all'){
        $riskFactors->where('eventyear',$year_input);
    }
    $riskFactors->groupby('riskfactors', 'riskindicators')
    ->orderby('riskfactors', 'ASC')
    ->orderby('riskindicators', 'ASC')
    ->get();
    $stateData = [
        'Overview' => '',
        'capital' => $stateData_pre[0]->capital,
        'female_est' => $stateData_pre[0]->female_est,
        'population_est' => $stateData_pre[0]->population_est,
        'region' => $stateData_pre[0]->region,
        'type' => array(),
        'zipcode' => $stateData_pre[0]->zipcode
    ];
        // State Basic Info
    foreach ($stateData_pre as $key => $value) {
        $stateData['type'][strtolower(str_replace(' ', '_', trim($value['type'])))] = $value['typesTotal'];
        $stateData['Overview'] = strip_tags($value['Overview']);
    }

        // Preapare incidents locations
    $stateData['incidents_positions'] = [];
    foreach ($incidents_location as $key => $value) {
        $stateData['incidents_positions'][] = [
            'lat'   => $value['lga_lat'],
            'long'  => $value['lga_long']
        ];
    }
        // Prepare riskfactors list
    $stateData['riskfactors'] = [];
//        {{ dd($riskFactors); }}
    if($riskFactors) {
        foreach ($riskFactors as $key => $value) {

            $stateData['riskfactors'][trim($value['riskfactors'])][trim($value['riskindicators'])] = $value['incidentsCount'];
                // Riskfactors Total Incidents
            if (!array_key_exists('total_incidents', $stateData['riskfactors'][$value['riskfactors']]))
                $stateData['riskfactors'][trim($value['riskfactors'])]['total_incidents'] = $value['incidentsCount'];
            else
                $stateData['riskfactors'][trim($value['riskfactors'])]['total_incidents'] += $value['incidentsCount'];
        }
    }
    return $stateData;
}
public function test(){
    $incidents_location = tbldataentry::select(['lga_lat', 'lga_long'])
    ->where('location', '=' ,'Abia')
    ->where('eventyear', '=' ,2019)
    ->where('lga_long', '<>', null)
    ->get();
    dd($incidents_location);
}
}
