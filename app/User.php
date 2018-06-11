<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
}
public function follow($userId)
{
    
    $exist = $this->is_following($userId);
    $its_me = $this->id == $userId;

    if ($exist || $its_me) {
        return false;
    } else {
        $this->followings()->attach($userId);
        return true;
    }
}

public function unfollow($userId)
{
    $exist = $this->is_following($userId);
    $its_me = $this->id == $userId;


    if ($exist && !$its_me) {
        // stop following if following
        $this->followings()->detach($userId);
        return true;
    } else {
        // do nothing if not following
        return false;
    }
}
    
    public function is_following($userId) {
    return $this->followings()->where('follow_id', $userId)->exists();
}


 public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorite', 'user_id', 'favorites_id')->withTimestamps();
    }

public function favorite($micropostsId)
{
    
    $exist = $this->is_favorite($micropostsId);
    //$its_me = $this->id == $micropostsId;

    if ($exist ) {
        return false;
    } else {
        $this->favorites()->attach($micropostsId);
        return true;
    }
}

public function unfavorite($micropostsId)
{
    $exist = $this->is_favorite($micropostsId);
    $its_me = $this->id == $micropostsId;


    if ($exist && !$its_me) {
        $this->favorites()->detach($micropostsId);
        return true;
    } else {
        return false;
    }
}


public function is_favorite($micropostsId) {
    return $this->favorites()->where('favorites_id', $micropostsId)->exists();
}

public function feed_microposts()
    {
        $favorite_user_ids = $this->favorites()-> pluck('users.id')->toArray();
        $favorite_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $favorites_user_ids);
    }


}
