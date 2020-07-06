<?php
namespace App\Library;

use Illuminate\Support\Arr;
use App\User;

class Permission {

    const PERMISSION_RECONCILE = 'reconcile';

    const PERMISSION_ADMIN = 'admin';

    const PERMISSION_BULKUPLOAD = 'bulkupload';

    const TRANSACTIONS_VIEW = 'transactions.view';

    const TEAM_VALEDRA = 'team-valedra';

    const TEAM_REYNOTT = 'team-reynott';

    const TEAM_HAYDEN_REYNOTT = 'team-hnr';

    const TEAM_ADMIN = 'team-admin';

    const TEAM_TITLES = [
        self::TEAM_VALEDRA => "Valedra",
        self::TEAM_HAYDEN_REYNOTT => "Hayden & Reynott",
        self::TEAM_REYNOTT => "Reynott",
    ];

    /**
     * Function fetches the users which are accessible by the team admin
     * @return array
     */
    public static function has_access_to_users_teams(){

        // Getting permissions of the logged in user
        $permissions = \Auth::user()->permissions ?? [];

        if(is_admin()) {
            $team_permissions = array_keys(self::TEAM_TITLES);
        } else {
            // Filtering team related permission
            $team_permissions = Arr::where($permissions, function ($value, $key) {
                return preg_match("/team-/", $value);
            });
        }


        $accessible_teams = $accessible_users = [];

            // If team admin is found in team permissions then get all the teams accessible to the admin
            // and users associated with all those teams.
            if (in_array(Permission::TEAM_ADMIN, $team_permissions) || is_admin()) {
                $accessible_teams = array_diff($team_permissions, [Permission::TEAM_ADMIN]);
                $Users = [];
                foreach ($accessible_teams as $team) {
                    $users = User::where('permissions', $team)->get(['_id'])->toArray();
                    $Users[] = $users;
                }
                $accessible_users = array_unique(array_merge(Arr::flatten($Users), [\Auth::user()->id]));
            }
        return [$accessible_users, $accessible_teams];
    }

    /**
     * Function for getting the owner and the person who uploaded the order.
     * Function checks for the user who uploaded the order and if found, checks whether there
     * is any owner associated with it. If yes, it checks whether the owner and uploaded by field is matched
     * then return the name of the uploaded by person if not returns the name of the owner and uploaded by person
     * concatenated.
     * @param $row
     * @return string
     */
    public static function order_owner($row)
    {
        $uploaded_by = User::where('_id',$row['uploaded_by'])->first();

        if(!empty($uploaded_by)) {
            if(!empty($row['owner']) && $row['owner'] != $row['uploaded_by']) {
                if($owner = User::where('_id', $row['owner'])->first()) {
                    return $owner;
                }
            }
            return $uploaded_by;
        }
        return '';
    }
}
