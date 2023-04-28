<?php

namespace App\Http\Controllers;

use App\tblanalysis;
use App\tbldataentry;
use App\tblweeklydataentry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function add_news(Request $req){
//        dd($req);
        try{
            //Insets in tblweeklydataentry
            $now = Carbon::now();
            $unique_code = $now->format('YmdHisu');
            $guessExtension = $req->file('image')->guessExtension();
            $path = $req->file('image')->storeAs('me', $unique_code.'.'.$guessExtension);
        $array = [
            'eventid' => $unique_code,
            'image' => env('APP_URL').'images/'.$path,
            'eventday' => Carbon::parse($req['news_date'])->format('d'),
            'eventmonth' => Carbon::parse($req['news_date'])->format('M'),
            'eventyear' => Carbon::parse($req['news_date'])->format('Y'),
            'week' => Carbon::parse($req['news_date'])->format('W'),
            'eventdate' => Carbon::parse($req['news_date'])->format('l'), //ll is short cut for date format Jan 5, 2017
            'eventdateToUse' => Carbon::parse($req['news_date'])->format('M d, Y'), // Jan 5, 2017
            'caption' => $req['news_caption'],
            'content' => $req['summary'],
            'location' => $req['state'],
            'lga' => $req['LGA'],
            'auditauthor' => auth('api')->user()->email,
            'auditdatecreated' => Carbon::now()->format('M d, Y'),
            'audittimecreated' => Carbon::now()->format('h:i:s a'),
            'news' => 'Yes',
            'riskfactors' =>$req['riskFactor'],
            'riskindicators' => $req['riskIndicator'],
//            'checked' => 'Yes'
        ];

        $add_news =  new tbldataentry();

        $add_news->save_data($array);
            //insert in tblanalysis
            $data = [
                'reportdate' => $req['news_date'],
                'caption' => $req['news_caption'],
                'content' => $req['summary'],
                'location' => $req['state'],
                'dweek' => $array['week'],
                'dyear' =>   $array['eventyear'],
                'analysisid' => $unique_code,
                'author' => auth('api')->user()->email,
                'type' => 'News',
                'lga' => $req['LGA'],
                'riskfactor' => $req['riskFactor'],
                'riskindicator' => $req['riskIndicator']
            ];
            $add_analysis = new tblanalysis();
            $add_analysis->insert($data);
            return response(['status' => 200,'statusText' => 'News Added Successfully']);
        }
        catch(Exception $exception)
        {
            throw new Exception($exception);

        }
    }
}
