<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function showImportForm()
    {
        $totalUsers = User::count();
        $addedUsers = 0;
        $updatedUsers = 0;
        return view('app',[
            'total' => $totalUsers,
            'updated'=>$updatedUsers,
            'added' =>$addedUsers,
        ]);
    }

    public function importUsers()
    {
        $apiData = file_get_contents('https://randomuser.me/api/?results=5000');
        $users = json_decode($apiData)->results;

        $newUsers = [];
        $updatedUsers = 0;

        foreach ($users as $userData) {
            $name = $userData->name->first;
            $lastname = $userData->name->last;
            $email = $userData->email;
            $age = $userData->dob->age;

            $user = [
                'first_name' => $name,
                'last_name' => $lastname,
                'email' => $email,
                'age' => $age,
            ];

            $existingUser = User::where('first_name', $name)
                ->where('last_name', $lastname)
                ->first();

            if ($existingUser) {
                $existingUser->update($user);
                $updatedUsers++;
            } else {
                $newUsers[] = $user;
            }
        }

        // Insert new users in bulk
        if (!empty($newUsers)) {
            User::insertOrIgnore($newUsers);
        }

        $totalUsers = User::count();
        $addedUsers = count($newUsers);

        return view('app', [
            'total' => $totalUsers,
            'updated' => $updatedUsers,
            'added' => $addedUsers,
        ]);
    }
}
