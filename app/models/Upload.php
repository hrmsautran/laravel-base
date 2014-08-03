<?php

class Upload extends Ardent {

  /**
   * Fillable columns
   */
  protected $fillable = [
    'name',
    'user_id',
    'uploadable_id',
    'uploadable_type',
    'type',
    'size',
    'url',
    'path',
  ];

  /**
   * These attributes excluded from the model's JSON form.
   * @var array
   */
  protected $hidden = [
    // 'password'
  ];

  /**
   * Validation Rules
   */
  private static $_rules = [
    'store' => [
      'uploadable_type' => 'required',
      'uploadable_id' => 'required',
      'name' => 'required',
      'type' => 'required',
      'size' => 'required',
      'url' => 'required',
      'path' => 'required',
    ]
  ];

  public static $rules = [];

  public static function setRules($name)
  {
    self::$rules = self::$_rules[$name];
  }

  /**
   * ACL
   */

  public static function canCreate() {
    return true;
  }

  public function canDelete() {
    $user = Auth::user();
    if($user->hasRole('Admin', 'Upload Admin'))
      return true;
    if($this->user_id === $user->id) {
      return true;
    }
    return false;
  }

  /**
   * Decorators
   */

  public function getSizeAttribute($bytes)
  {
    $decimals = 2;
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) .' '. @$size[$factor];
  }

  /**
   * Relationships
   */
  
  public function uploadable()
  {
      return $this->morphTo();
  }

  /**
   * Boot Method
   */

  public static function boot()
  {
    parent::boot();

    static::creating(function($data){
      $data->user_id = Auth::user()->id;
    });

    static::created(function(){
      Cache::tags('Upload')->flush();
    });

    static::updated(function(){
      Cache::tags('Upload')->flush();
    });

    static::deleted(function(){
      Cache::tags('Upload')->flush();
    });
  }

}