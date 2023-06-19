<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);


        $query = Team::query();

        if ($id) {
            $team = $query->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team with id found');
            }
            return ResponseFormatter::error('Team not found', 404);
        }

        $team = $query->where('company_id', $request->company_id);
        if ($name) {
            $team = Team::where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success($team->paginate($limit), 'Teams Found');
    }

    public function create(TeamRequest $request)
    {
        try {
            if ($request->file('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if (!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(TeamRequest $request, $id)
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            // upload icon
            if ($request->file('icon')) {
                // delete icon
                Storage::delete($team->icon);

                // add new icon
                $path = $request->file('icon')->store('public/icons');
            }

            // update team with update
            $team->update([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            // return
            return ResponseFormatter::success($team, 'Team Updated Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $team = Team::find($id);
            if (!$team) {
                throw new Exception('Team not found');
            }

            // check if team is owned by user
            $currentUser = User::find(Auth::id());
            if (!$currentUser->companies()->where('company_user.company_id', $team->company_id)->exists()) {
                throw new Exception('Team not owned by user');
            }

            // delete team
            $team->delete();
            return ResponseFormatter::success($team, 'Team Deleted Successfuly');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
