<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tblweeklydataentry extends Model
{
    protected $table = 'tblweeklydataentry';
    public $timestamps = false;
//];protected $fillable = ['director2','lga','lga_lat','lga_long','link2','author2','link3','author3','link4','author4','link5','author5','status','location','auditdatecreated','audittimecreated','checked','hashtag','news','Casualties_pointer','Casualties_count','Injuries_pointer','Injuries_count','latitude','longitude','city','accused','victim','dday', 'dmonth','dyear','dweek','reportdate','caption','content','location','lga','auditauthor','auditdatecreated','audittimecreated','news','riskfactor','riskindicator','checked'];
    protected $guarded = [];
    public function save_data($data){
        return tblweeklydataentry::insert($data);
    }
}
