<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, CanUseCreator, CanUseMeta;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'first_name', 'last_name', 'display_name', 'email', 'password', 'phone',
        'date_of_birth', 'id_card', 'id_card_issued_date', 'id_card_expired_date',
        'id_card_issued_by', 'gender', 'roll_no', 'photo', 'postcode', 'address', 'state',
        'country', 'media', 'department_id', 'categories', 'expired_date', 'role_id', 'permissions',
        'remember_token', 'status', 'creator_id','created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    // protected $guarded = ['_token', 'password_confirmation'];

    public static function create(array $attributes = [])
    {
        if ( ! isset($attributes['display_name']) && ! empty($attributes['first_name']) && ! empty($attributes['last_name']))
            $attributes['display_name'] = ucfirst($attributes['first_name']) . ' ' . ucfirst($attributes['last_name']);

        parent::create($attributes);
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function branches()
    {
        return $this->belongsToMany('App\Branch', 'users_branches')
                    ->withPivot('creator_id')
                    ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany('App\Classes', 'users_classes', 'class_id', 'user_id');
    }

    public function programs()
    {
        return $this->belongsToMany('App\Program', 'users_programs')
                    ->withPivot('status', 'creator_id')
                    ->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany('App\Subject', 'users_subjects')
                    ->withPivot('class_id', 'attendance_detail', 'grade_detail', 'status', 'creator_id', 'branch_id')
                    ->withTimestamps();
    }

    /**
     * Check if user belongs to Role. Role can be slug or id
     * 
     * @param  Integer/String  $role Role ID or Slug
     * 
     * @return boolean
     */
    public function isRole($role)
    {
        if (is_numeric($role))
            return $this->role_id == $role;

        return Role::whereSlug($role)->exists();
    }

    /**
     * Check if current user is student
     * 
     * @return boolean
     */
    public function isStudent()
    {
        return $this->isRole(4);
    }

    public function isTeacher()
    {
        return $this->isRole(3);
    }

    public function isParent()
    {
        return $this->isRole(5);
    }

    public function isSuperAdmin()
    {
        return $this->isRole(1);
    }

    public function isAdmin()
    {
        return $this->isRole(2);
    }

    /**
     * If user full name is set, return his full name. Otherwise, return username
     * 
     * @return String user name
     */
    public function getFullName()
    {
        if (! empty($this->first_name) || ! empty($this->last_name))
            return $this->first_name . ' ' . $this->last_name;

        return $this->name;
    }

    /**
     * Get User Photo if exists. Otherwise, if Gravatar enabled, use gravatar, other wise use default image
     * 
     * @return String Photo Image URL
     */
    public function getPhotoAttribute($value)
    {
        $use_gravatar = true;
        $default_photo = '';

        if ($use_gravatar)
            $default_photo = 'http://www.gravatar.com/avatar/' . md5($this->email) . '?s=80';

        if ( ! empty($value) && file_exists(base_path() . '/public/photos/' . $value))
            return url('/photos/' . $value);

        return $default_photo;
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function getDisplayNameAttribute($value)
    {
        if (empty($value))
            return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
        
        return $value;
    }

    public function uploadPhoto($photo_path, $photo_name = '')
    {
        //
    }

    public function getTeacherSubjects()
    {

    }

    public function scopeSearch($query, $value)
    {
        if (! empty($value)) {
            $search = "%$value%";

            return $query->whereId($value)
                        ->orwhere('name', 'like', $search)
                        ->orWhere('display_name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'lile', $search)
                        ->orWhere('id_card', 'like', $search);
        }

        return $query;
    }

    public function scopeOfProgram($query, $value)
    {
         if (is_numeric($value) && $value > 0)
            return $query->has('programs', '=', $value);

        return $query;
    }

    public function scopeOfBranch($query, $value)
    {
        if (is_numeric($value) && $value > 0)
            return $query->has('branches', '=', $value);

        return $query;
    }

    public function scopeOfRole($query, $value)
    {
        if (is_numeric($value) && $value > 0)
            return $query->whereRoleId($value);

        return $query;
    }


}