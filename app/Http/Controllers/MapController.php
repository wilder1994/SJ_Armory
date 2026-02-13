<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isResponsible() && !$user->isAuditor())) {
            abort(403);
        }

        return view('maps.index');
    }

    public function weapons(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isResponsible() && !$user->isAuditor())) {
            abort(403);
        }

        $query = Weapon::query()->with([
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker.client',
        ]);

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('clientAssignments', function ($assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id)->where('is_active', true);
            });
        }

        $weapons = $query->get();

        $data = $weapons->map(function ($weapon) {
            $post = $weapon->activePostAssignment?->post;
            $worker = $weapon->activeWorkerAssignment?->worker;
            $client = $weapon->activeClientAssignment?->client;

            $lat = null;
            $lng = null;
            $locationLabel = null;

            if ($post && $post->latitude && $post->longitude) {
                $lat = (float) $post->latitude;
                $lng = (float) $post->longitude;
                $locationLabel = $post->name;
            } elseif ($worker && $worker->client && $worker->client->latitude && $worker->client->longitude) {
                $lat = (float) $worker->client->latitude;
                $lng = (float) $worker->client->longitude;
                $locationLabel = $worker->client->name;
            } elseif ($client && $client->latitude && $client->longitude) {
                $lat = (float) $client->latitude;
                $lng = (float) $client->longitude;
                $locationLabel = $client->name;
            }

            if (!$lat || !$lng) {
                return null;
            }

            return [
                'id' => $weapon->id,
                'internal_code' => $weapon->internal_code,
                'serial_number' => $weapon->serial_number,
                'client' => $client?->name,
                'responsible' => $weapon->activeClientAssignment?->responsible?->name,
                'location' => $locationLabel,
                'lat' => $lat,
                'lng' => $lng,
                'link' => route('weapons.show', $weapon),
            ];
        })->filter()->values();

        return response()->json($data);
    }
}

