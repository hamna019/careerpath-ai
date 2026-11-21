<?php

namespace App\Http\Controllers;

use App\Models\CareerDetails;
use Illuminate\Http\Request;

class CareerDetailsController extends Controller
{
    public function index(Request $request) {
        $query = CareerDetails::query();

        $limit = (int) $request->query('limit', 0);
        if ($limit > 0) {
            $query->orderBy('id')->limit($limit);
        }

        return response()->json($query->get());
    }

    public function store(Request $request) {
        $careerDetails = CareerDetails::create($request->all());
        return response()->json([
            'message' => 'Career saved successfully in Database!', 
            'data' => $careerDetails
        ]);
    }

    public function show($id) {
        return response()->json(CareerDetails::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $careerDetails = CareerDetails::findOrFail($id);
        $careerDetails->update($request->all());
        return response()->json([
            'message' => 'Career updated successfully!', 
            'data' => $careerDetails
        ]);
    }

    public function destroy($id) {
        CareerDetails::destroy($id);
        return response()->json([
            'message' => 'Career completely deleted from Database!'
        ]);
    }
}