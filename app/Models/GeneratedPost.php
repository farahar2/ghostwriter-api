<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedPost extends Model
{
    protected $fillable = [
      'raw_content_id',
      'hook_propose',
      'body_points',
      'technical_readability_score',
      'suggested_hashtags',
      'tone_compliance_justification',
      'status'
    ];

    protected $casts = [
    'body_points' => 'array',
    'suggested_hashtags' => 'array',
];
   
    public function rawContent()
{
    return $this->belongsTo(RawContent::class);
}

}

