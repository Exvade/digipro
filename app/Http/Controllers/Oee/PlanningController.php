<?php

namespace App\Http\Controllers\Oee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningController extends Controller
{
    /**
     * Display a listing of production plans.
     */
    public function index(): View
    {
        return view('oee.planning.index');
    }

    /**
     * Show the form for creating a new production plan.
     */
    public function create(): View
    {
        return view('oee.planning.create');
    }

    /**
     * Store a newly created production plan in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'productCode' => 'required|string|max:50',
            'productName' => 'required|string|max:255',
            'targetQuantity' => 'required|numeric|min:1',
            'unitOfMeasure' => 'required|string|max:20',
            'priority' => 'required|in:low,medium,high,urgent',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'machineId' => 'required|string|max:50',
            'planningLocation' => 'required|in:oee,control_leader',
            'shift' => 'required|in:morning,afternoon,night',
            'operator' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Here you would typically save to database
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning created successfully!');
    }

    /**
     * Display the specified production plan.
     */
    public function show(string $planning): View
    {
        return view('oee.planning.show', compact('planning'));
    }

    /**
     * Show the form for editing the specified production plan.
     */
    public function edit(string $planning): View
    {
        return view('oee.planning.edit', compact('planning'));
    }

    /**
     * Update the specified production plan in storage.
     */
    public function update(Request $request, string $planning)
    {
        // Validate the request
        $validated = $request->validate([
            'productCode' => 'required|string|max:50',
            'productName' => 'required|string|max:255',
            'targetQuantity' => 'required|numeric|min:1',
            'unitOfMeasure' => 'required|string|max:20',
            'priority' => 'required|in:low,medium,high,urgent',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'machineId' => 'required|string|max:50',
            'planningLocation' => 'required|in:oee,control_leader',
            'shift' => 'required|in:morning,afternoon,night',
            'operator' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Here you would typically update the database
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning updated successfully!');
    }

    /**
     * Remove the specified production plan from storage.
     */
    public function destroy(string $planning)
    {
        // Here you would typically delete from database
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning deleted successfully!');
    }

    /**
     * Display the production schedule view.
     */
    public function schedule(): View
    {
        return view('oee.planning.schedule');
    }

    /**
     * Display the planning analysis view.
     */
    public function analysis(): View
    {
        return view('oee.planning.analysis');
    }

    /**
     * Export production planning data.
     */
    public function export(Request $request)
    {
        // Here you would typically generate and download an export file
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning data exported successfully!');
    }

    /**
     * Start a production plan.
     */
    public function start(string $planning)
    {
        // Here you would typically update the status to 'in-progress'
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning started successfully!');
    }

    /**
     * Pause a production plan.
     */
    public function pause(string $planning)
    {
        // Here you would typically update the status to 'paused'
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning paused successfully!');
    }

    /**
     * Complete a production plan.
     */
    public function complete(string $planning)
    {
        // Here you would typically update the status to 'completed'
        // For now, we'll just redirect back with success message
        return redirect()->route('oee.planning.index')
            ->with('success', 'Production planning completed successfully!');
    }
} 