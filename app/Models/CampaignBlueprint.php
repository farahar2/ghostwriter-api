<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignBlueprint extends Model
{
  use HasFactory;

  protected $fillable = [
  'name',
  'tone_description',
  'max_characters',
  'max_hashtags',
  'regle_supp'
  ];
    
  public function user(){
    return $this->belongsTo(User::class);
  }

  public function rawContents(){
    return $this->hasMany(RawContent::class);
  }
}
