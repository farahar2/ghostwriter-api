<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawContent extends Model
{
    protected $fillable = [
      'content',
      'status'
    ];

  public function user(){
    return $this->belongsTo(User::class);
  }

  public function campaignBlueprint(){
    return $this->belongsTo(CampaignBlueprint::class);
  }

  public function generatedPosts(){
    return $this->hasMany(GeneratedPost::class);
  }
  
}
