<?php
namespace App\Library;

use App\User;
use Illuminate\Support\Arr;

class Permission {

    const PERMISSION_RECONCILE = 'reconcile';

    const PERMISSION_ADMIN = 'admin';

    const PERMISSION_BULKUPLOAD = 'bulkupload';

    const TEAM_ADMIN = 'team-admin';


    public static function has_access_to_users_teams(){

        // Getting permissions of the logged in user
        $permissions = !empty(\Auth::user()->permissions) ? \Auth::user()->permissions : [];

                // Filtering team related permission
        $team_permissions = Arr::where($permissions, function ($value, $key) {
                            return preg_match("/team/", $value);
        });

        // If team admin is found in team permissions then get all the teams accessible to the admin
        // and users associated with all those teams.
        if(in_array(Permission::TEAM_ADMIN,$team_permissions)){
            $accessible_teams = array_diff($team_permissions,[Permission::TEAM_ADMIN]);
            $Users = [];
            foreach ($accessible_teams as $team) {
                $users = User::where('permissions',$team)->get(['_id'])->toArray();
                $Users[] = $users;
            }
            $accessible_users = array_diff(array_unique(Arr::flatten($Users)),[\Auth::user()->id]);
            return [$accessible_users,$accessible_teams];
        }
        return [];
    }
}
