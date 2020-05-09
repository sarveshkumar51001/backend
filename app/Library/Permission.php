<?php
namespace App\Library;

use Illuminate\Support\Arr;
use App\User;

class Permission {

    const PERMISSION_RECONCILE = 'reconcile';

    const PERMISSION_ADMIN = 'admin';

    const PERMISSION_BULKUPLOAD = 'bulkupload';

    const TEAM_VALEDRA = 'team-valedra';

    const TEAM_REYNOTT = 'team-reynott';

    const TEAM_HAYDEN_REYNOTT = 'team-h&r';

    const TEAM_ADMIN = 'team-admin';


    /**
     * Function fetches the users which are accessible by the team admin
     * @return array
     */
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

    /**
     * Function for getting the owner and the person who uploaded the order.
     * Function checks for the user who uploaded the order and if found, checks whether there
     * is any owner associated with it. If yes, it checks whether the owner and orde
     * @param $row
     * @return string
     */
    public static function order_owner($row)
    {
        $uploaded_by = User::where('_id',$row['uploaded_by'])->first();

        if(!empty($uploaded_by)) {
            if(isset($row['owner'])){
                if($row['owner'] == $row['uploaded_by']){
                    return $uploaded_by['name'];
                } else {
                    $owner = User::where('_id', $row['owner'])->first();
                    if ($owner) {
                        return sprintf("%s (Uploaded By - %s)", $owner['name'], $uploaded_by['name']);
                    }
                }
            }
            return $uploaded_by['name'];
        }
    }

}
